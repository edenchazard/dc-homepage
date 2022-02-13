<?php
require_once "vendor/autoload.php";

$yourSiteUrl = 'https://chazza.me/dc';

// Setting the current working directory to be output directory
// for generated sitemaps (and, if needed, robots.txt)
// The output directory setting is optional and provided for demonstration purposes.
// The generator writes output to the current directory by default. 
$outputDir = getcwd();

$generator = new \Icamys\SitemapGenerator\SitemapGenerator($yourSiteUrl, $outputDir);

// Create a compressed sitemap
//$generator->enableCompression();

// Determine how many urls should be put into one file;
// this feature is useful in case if you have too large urls
// and your sitemap is out of allowed size (50Mb)
// according to the standard protocol 50000 urls per sitemap
// is the maximum allowed value (see http://www.sitemaps.org/protocol.html)
$generator->setMaxUrlsPerSitemap(50000);

// Set the sitemap file name
$generator->setSitemapFileName("sitemap.xml");

// Set the sitemap index file name
$generator->setSitemapIndexFileName("sitemap-index.xml");

// // Add alternate languages if needed
// $alternates = [
//     ['hreflang' => 'de', 'href' => "http://www.example.com/de"],
//     ['hreflang' => 'fr', 'href' => "http://www.example.com/fr"],
// ];

// Add url components: `path`, `lastmodified`, `changefreq`, `priority`, `alternates`
// Instead of storing all urls in the memory, the generator will flush sets of added urls
// to the temporary files created on your disk.
// The file format is 'sm-{index}-{timestamp}.xml'
$generator->addURL('/', new DateTime(filemtime("/views/home.twig")), 'always', 1);
$generator->addURL('/prizes', new DateTime(filemtime("/views/prizes.twig")), 'always', 0.5);
$generator->addURL('/tools', new DateTime(filemtime("/views/tools.twig")), 'always', 0.7);
$generator->addURL('/trading', new DateTime(filemtime("/views/trading.twig")), 'always', 0.5);
$generator->addURL('/lineage-builder', new DateTime(), 'always', 0.9);
$generator->addURL('/auto-refresher', new DateTime(), 'always', 0.9);

// Flush all stored urls from memory to the disk and close all necessary tags.
$generator->flush();

// Move flushed files to their final location. Compress if the option is enabled.
$generator->finalize();

// Update robots.txt file in output directory or create a new one
$generator->updateRobots();

// Submit your sitemaps to Google, Yahoo, Bing and Ask.com
$generator->submitSitemap();
?>