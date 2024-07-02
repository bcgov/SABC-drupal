<?php
declare(strict_types=1);

namespace Drupal\sabc_institution;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;


class SabcServiceProvider extends ServiceProviderBase {

  public function register(ContainerBuilder $container) {
    $containerModules = $container->getParameter('container.modules');
    $finder = new Finder();

    $foldersWithServiceContainers = [];

    $foldersWithServiceContainers['Drupal\sabc_institution\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/')->files()->name('*.php');

    $foldersWithServiceContainers['Drupal\sabc_institution\Transformer\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/Transformer/')->files()->name('*.php');

    $foldersWithServiceContainers['Drupal\sabc_institution\DomCrawler\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/DomCrawler/')->files()->name('*.php');
    $foldersWithServiceContainers['Drupal\sabc_institution\MongoDBFetcher\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/MongoDBFetcher/')->files()->name('*.php');

    $foldersWithServiceContainers['Drupal\sabc_institution\Importer\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/Importer/')->files()->name('*.php');
    $foldersWithServiceContainers['Drupal\sabc_institution\Importer\MongoDB\\'] = $finder->in(dirname($containerModules['sabc_institution']['pathname']) . '/src/Importer/MongoDB/')->files()->name('*.php');

    foreach ($foldersWithServiceContainers as $namespace => $files) {
      foreach ($files as $fileInfo) {
        // remove .php extension from filename
        $class = $namespace
          . substr($fileInfo->getFilename(), 0, -4);
        // don't override any existing service
        if ($container->hasDefinition($class)) {
          continue;
        }
        $definition = new Definition($class);
        $definition->setAutowired(TRUE);
        $container->setDefinition($class, $definition);
      }
    }
  }

}