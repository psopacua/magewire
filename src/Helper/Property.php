<?php declare(strict_types=1);
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

namespace Magewirephp\Magewire\Helper;

use Magento\Framework\Stdlib\ArrayManager;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Exception\ComponentException;

class Property
{
    protected ArrayManager $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function containsDots(string $property): bool
    {
        return strpos($property, '.') !== false;
    }

    /**
     * @throws ComponentException
     */
    public function transformDots(string $path, $value, Component $component): array
    {
        $property = strstr($path, '.', true);
        $realpath = $path;

        if (!array_key_exists($property, $component->getPublicProperties())) {
            throw new ComponentException(__('Public property %1 does\'nt exist', [$property]));
        }

        $path   = substr(strstr($path, '.'), 1);
        $data   = $this->arrayManager->set($path, $component->{$property}, $value, '.');

        return compact('property', 'data', 'realpath', 'path');
    }

    public function searchViaDots(string $path, array $value)
    {
        return $this->arrayManager->get($path, $value, null, '.') ?? $default;
    }

    /**
     * Use a callback function to assign component property
     * values except default reserved properties.
     */
    public function assign(callable $callback, Component $component, array $data = null): void
    {
        $publicProperties = $component->getPublicProperties(true);
        $data = $data === null ? $publicProperties : array_merge($publicProperties, $data);

        foreach ($data as $property => $value) {
            if (in_array($property, Component::RESERVED_PROPERTIES, true)) {
                continue;
            }
            if (array_key_exists($property, $publicProperties)) {
                $callback($component, $property, $value);
            }
        }
    }

    public function resyncPropsWithRequestData(Component $component): Component
    {
        return $component;
    }
}
