<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector;
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
    // register a single rule
    $rectorConfig->rule(ClassPropertyAssignToConstructorPromotionRector::class);
    $rectorConfig->rule(ContainerGetToConstructorInjectionRector::class);
    $rectorConfig->rule(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
    $rectorConfig->rule(RemoveUnusedPrivateMethodParameterRector::class);
    $rectorConfig->rule(ActionInjectionToConstructorInjectionRector::class);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route')]
    );
};
