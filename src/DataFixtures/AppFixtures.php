<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use App\Entity\Product;
use App\Entity\Property;
use App\Entity\PropertyValue;
use App\Entity\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use http\Exception\InvalidArgumentException;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class AppFixtures extends Fixture
{
    const DEMO_DATA_URL = 'http://b12.skillum.ru/bitrix/catalog_export/intarocrm.xml';

    const UPLOAD_DIR = __DIR__ . '/../../public/upload/pictures';

    const OFFERS_COUNT = 500;

    public function load(ObjectManager $manager)
    {
        $output = new ConsoleOutput();
        $simpleXml = simplexml_load_string(file_get_contents(self::DEMO_DATA_URL));
        $output->writeln("xml loaded successfully");
        if (empty($simpleXml)) {
            throw new InvalidArgumentException("Unable to load xml from URL " . self::DEMO_DATA_URL);
        }
        $filesystem = new Filesystem();

        if (!is_writable(self::UPLOAD_DIR)) {
            throw new FileNotWritableException("Upload directory is not writable. Check file permissions");
        }
        $filesystem->remove(glob(self::UPLOAD_DIR . "/*"));
        $output->writeln("upload directory cleaned");

        $xmlCategories = $simpleXml->shop->categories->category;
        $sections = [];
        foreach ($xmlCategories as $category) {
            $section = new Section();
            $section->setName($category->getName());
            $xmlId = (string)$category->attributes()->id ?? null;
            $section->setXmlId($xmlId);
            $sections[$xmlId] = $section;
            $manager->persist($section);
        }
        
        foreach ($xmlCategories as $category) {
            $parentXmlId = (string)$category->attributes()->parentId ?? null;
            if (!$parentXmlId) {
                continue;
            }
            $xmlId = (string)$category->attributes()->id ?? null;
            $parent = $sections[$parentXmlId];
            $section = $sections[$xmlId];
            $section->setParent($parent);
            $manager->persist($section);
        }
        $output->writeln("categories processed");
        
        $properties = [];
        $products = [];
        $offers = [];
        $propSort = 0;
        $xmlOffers = $simpleXml->shop->offers->offer;
        $productCount = 0;
        $output->writeln("offers processing...");
        $progressBar = new ProgressBar($output, min(count($xmlOffers) , self::OFFERS_COUNT));
        foreach ($xmlOffers as $xmlOffer) {
            if (++$productCount > self::OFFERS_COUNT) {
                break;
            }

            $offerXmlId = (string)$xmlOffer->attributes()->id;
            $productXmlId = (string)$xmlOffer->attributes()->productId;
            $sectionXmlId = (string)$xmlOffer->categoryId;

            if (!isset($products[$productXmlId])) {
                $product = new Product();
                $product->setXmlId($productXmlId);
                $product->setName((string)$xmlOffer->productName);
                $product->setVatRate((float)$xmlOffer->vatRate ?: 20.00);
                $product->setActive(true);
                $product->setVendor((string)$xmlOffer->vendor);
                $product->addSection($sections[$sectionXmlId] ?? null);
                $manager->persist($product);
                $products[$productXmlId] = $product;
            } else {
                $product = $products[$productXmlId];
            }

            $offer = new Offer();
            $offer->setActive(true);
            $offer->setName((string)$xmlOffer->name);
            $offer->setXmlId($offerXmlId);
            $offer->setPrice((float)$xmlOffer->price);
            $offer->setProduct($product);
            $offer->setUnit('шт.');
            $offer->setQuantity((int)$xmlOffer->attributes()->quantity);

            $pictureUrl = (string)$xmlOffer->picture[0];
            if (!empty($pictureUrl)) {
                $offer->setPicture($this->savePicture($pictureUrl));
            }

            foreach ($xmlOffer->param as $xmlParam) {
                $code = (string)$xmlParam->attributes()->code ?: \Transliterator::create("tr-Lower")->transliterate($xmlParam->attributes()->name);
                if (!isset($properties[$code])) {
                    $property = new Property();
                    $property->setName((string)$xmlParam->attributes()->name);
                    $property->setCode($code);
                    $property->setSort($propSort++);
                    $properties[$code] = $property;
                    $manager->persist($property);
                } else {
                    $property = $properties[$code];
                }
                
                $propertyValue = new PropertyValue();
                $propertyValue->setProperty($property);
                $propertyValue->setValue((string)$xmlParam);
                $manager->persist($propertyValue);
                $offer->addPropertyValue($propertyValue);

            }
            $manager->persist($offer);
            $offers[$offerXmlId] = $offer;
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln("");

        $output->writeln("Flush to database...");
        $manager->flush();
        $output->writeln("Flush to database finished");
    }

    private function savePicture(string $pictureUrl) : ?string
    {
        $filesystem = new Filesystem();
        if (empty($pictureUrl)) {
            return null;
        }

        $fileContent =  file_get_contents($pictureUrl);
        if (empty($fileContent)) {
            return null;
        }
        $tempName = $filesystem->tempnam('/tmp', 'offer_picture_');
        $filesystem->dumpFile($tempName, $fileContent);

        $fileData = pathinfo($pictureUrl);
        $file = new UploadedFile($tempName, $fileData['basename']);
        if ($file->guessExtension() !== 'jpeg' || $file->getSize() > 10 * 1024 * 1024) {
            return null;
        }
        $newFileName = sha1($pictureUrl . uniqid()) . '.jpeg';
        $dir = substr($newFileName, 0, 2);
        if (!$filesystem->exists(self::UPLOAD_DIR . '/' . $dir)) {
            $filesystem->mkdir(self::UPLOAD_DIR . '/' . $dir);
        }
        try {
            $filesystem->rename($tempName, self::UPLOAD_DIR . '/' . $dir . '/' . $newFileName);
        } catch (\Exception $e){
            return null;
        }
        return $dir . '/' . $newFileName;
    }
}
