<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // Define what rule sets will be applied
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);

    // get services (needed for register a single rule)
    $services = $rectorConfig->services();

    // register a single rule
    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);
    $services->set(ContainerGetToConstructorInjectionRector::class);
    $services->set(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
    $services->set(RemoveUnusedPrivateMethodParameterRector::class);
    $services->set(AnnotationToAttributeRector::class)
        ->configure([new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route')]);
};
