<?php
/**
 * This file is part of ONP.
 *
 * Copyright (c) 2014 Opensoft (http://opensoftdev.com)
 *
 * The unauthorized use of this code outside the boundaries of
 * Opensoft is prohibited.
 */

namespace Opensoft\StorageBundle\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Gaufrette\Adapter;
use Opensoft\StorageBundle\Storage\Adapter\AdapterConfigurationInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class GaufretteAdapterResolver
{
    /**
     * @var ArrayCollection|AdapterConfigurationInterface[]
     */
    protected $configurations;

    public function __construct()
    {
        $this->configurations = new ArrayCollection();
    }

    /**
     * @param AdapterConfigurationInterface $adapterConfiguration
     */
    public function addConfiguration(AdapterConfigurationInterface $adapterConfiguration)
    {
        $this->configurations->set(get_class($adapterConfiguration), $adapterConfiguration);
    }

    /**
     * @param string $class
     * @return AdapterConfigurationInterface
     */
    public function getConfigurationByClass($class)
    {
        return $this->configurations->get($class);
    }

    /**
     * @return ArrayCollection|AdapterConfigurationInterface[]
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @return array
     */
    public function getAdapterChoices()
    {
        $choices = [];
        foreach ($this->configurations as $class => $configuration) {
            $choices[$class] = $configuration::getName();
        }

        return $choices;
    }

    /**
     * @param array $options
     * @throws InvalidOptionsException|MissingOptionsException|\InvalidArgumentException
     * @return Adapter
     */
    public function getAdapter(array $options)
    {
        $configuration = $this->getConfigurationByClass($options['class']);
        if (!$configuration) {
            throw new \InvalidArgumentException(sprintf("Class '%s' is not a valid adapter configuration", $options['class']));
        }

        unset($options['class']);

        return $configuration->createAdapter($options);
    }
}
