<?php

namespace VSHF\Config;

/**
 * Class Config
 */
final class Config
{
    public const CONTEXT_APP = 'app';

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var array
     */
    private $observers = [];

    /**
     * @var array
     */
    private $propertyObservers = [];

    /**
     * @param array $hydrateApp
     */
    public function __construct(array $hydrateApp = [])
    {
        $this->settings[ self::CONTEXT_APP ] = $hydrateApp;
    }

    /**
     * Used to hydrate the settings array with the provided data in the specified context.
     *
     * @param array  $data    An array containing the data to be hydrated.
     * @param string $context The context in which the data should be hydrated. Defaults to the application context.
     *
     * @return void
     */
    public function hydrate(array $data, string $context = self::CONTEXT_APP): void
    {
        $this->settings[ $context ] = $data;
    }

    /**
     * Used to hydrate the properties array with the provided data for a specific resource in the
     * specified context.
     *
     * @param array  $data       An array containing the data to be hydrated into the properties array.
     * @param string $context    The context in which the data should be hydrated.
     * @param string $resourceId The identifier of the resource for which the data should be hydrated.
     *
     * @return void
     */
    public function hydrateResource(array $data, string $context, string $resourceId): void
    {
        $this->properties[ $context ][ $resourceId ] = $data;
    }

    /**
     * Used to drop the properties array for a specific resource in the specified context.
     *
     * @param string $context    The context in which the properties array should be dropped.
     * @param string $resourceId The identifier of the resource for which the data should be dropped.
     *
     * @return void
     */
    public function dropResource(string $context, string $resourceId): void
    {
        unset($this->properties[ $context ][ $resourceId ]);
    }

    /**
     * Used to drop the resources for the specified context.
     *
     * @param string $context The context in which the resources should be dropped.
     *
     * @return void
     */
    public function dropResources(string $context): void
    {
        unset($this->properties[ $context ]);
    }

    /**
     * Used to register an observer class for a specific setting in the specified context.
     *
     * @param string $settingId         The identifier of the setting to register the observer for.
     * @param string $observerClassName The name of the observer class to register.
     * @param string $context           The context in which the observer should be registered. Defaults to the
     *                                  application context.
     *
     * @return void
     *
     * @throws \UnexpectedValueException if the specified setting is already registered with an observer.
     */
    public function registerObserver(string $settingId, string $observerClassName, string $context = self::CONTEXT_APP): void
    {
        if (isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is already registered.");
        }

        $this->observers[ $context ][ $settingId ] = $observerClassName;
    }

    /**
     * Used to register an observer class for a specific property setting in the specified context.
     *
     * @param string $settingId         The identifier of the property setting to register the observer for.
     * @param string $observerClassName The name of the observer class to register.
     * @param string $context           The context in which the observer should be registered.
     *
     * @return void
     *
     * @throws \UnexpectedValueException if the specified property setting is already registered with an observer.
     */
    public function registerPropertyObserver(string $settingId, string $observerClassName, string $context): void
    {
        if (isset($this->propertyObservers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is already registered.");
        }

        $this->propertyObservers[ $context ][ $settingId ] = $observerClassName;
    }

    /**
     * Retrieves all the settings in raw format for the specified context.
     *
     * @param string $context (Optional) The context for which to retrieve the settings. Defaults to the application
     *                        context.
     *
     * @return array An array containing all the settings for the specified context. If no settings are found, an empty
     *               array is returned.
     */
    public function getAllSettingsByContextRaw(string $context = self::CONTEXT_APP): array
    {
        return $this->settings[ $context ] ?? [];
    }

    /**
     * @deprecated This method is deprecated. Please use getAllSettingsByContextRaw() instead.
     */
    public function getAllByContextRaw(string $context = self::CONTEXT_APP): array
    {
        trigger_error(
            'The method getAllByContextRaw() is deprecated. Please use getAllSettingsByContextRaw() instead.',
            E_USER_DEPRECATED
        );

        return $this->getAllSettingsByContextRaw($context);
    }

    /**
     * Retrieves all the properties in raw format for the specified context.
     *
     * @param string $context The context for which to retrieve the properties.
     *
     * @return array An array containing all the properties for the specified context. If no properties are found, an
     *               empty array is returned.
     */
    public function getAllPropertiesByContextRaw(string $context): array
    {
        return $this->properties[ $context ] ?? [];
    }

    /**
     * Retrieves all the settings for the specified context.
     *
     * @param string $context (Optional) The context for which to retrieve the settings. Defaults to the application
     *                        context.
     *
     * @return array An array containing all the settings for the specified context. If no settings are found or the
     *               context has no observers, an empty array is returned.
     */
    public function getAllSettingsByContext(string $context = self::CONTEXT_APP): array
    {
        if (!isset($this->observers[ $context ])) {
            return [];
        }

        $settings = [];

        foreach ($this->observers[ $context ] as $settingId => $observer) {
            $settings[ $settingId ] = $this->get($settingId, $context);
        }

        return $settings;
    }

    /**
     * @deprecated This method is deprecated. Please use getAllSettingsByContext() instead.
     */
    public function getAllByContext(string $context = self::CONTEXT_APP): array
    {
        trigger_error(
            'The method getAllByContext() is deprecated. Please use getAllSettingsByContext() instead.',
            E_USER_DEPRECATED
        );

        return $this->getAllSettingsByContext($context);
    }

    /**
     * Retrieves all the properties for the specified context.
     *
     * @param string $context The context for which to retrieve the properties.
     *
     * @return array An array containing all the properties for the specified context. If no properties are found or the
     *               context has no observers, an empty array is returned.
     */
    public function getAllPropertiesByContext(string $context): array
    {
        if (!isset($this->propertyObservers[ $context ], $this->properties[ $context ])) {
            return [];
        }

        $settings = [];

        foreach ($this->propertyObservers[ $context ] as $settingId => $observer) {
            foreach ($this->properties[ $context ] as $resourceId => $properties) {
                $settings[ $resourceId ][ $settingId ] = $this->getProperty($settingId, $context, $resourceId);
            }
        }

        return $settings;
    }

    /**
     * Checks if a resource exists in the specified context.
     *
     * @param string $context    The context to check for the existence of the resource.
     * @param string $resourceId The identifier of the resource to check.
     *
     * @return bool Returns true if the resource exists in the specified context, false otherwise.
     */
    public function resourceExists(string $context, string $resourceId): bool
    {
        return isset($this->properties[ $context ][ $resourceId ]);
    }

    /**
     * Retrieves the properties of a resource in the specified context.
     *
     * @param string $context    The context in which the resource exists.
     * @param string $resourceId The identifier of the resource to retrieve the properties for.
     *
     * @return array An array containing the properties of the specified resource in the specified context. If the
     *               context or the resource doesn't have any property observers or if the resource doesn't exist, an
     *               empty array is returned.
     */
    public function getResourceProperties(string $context, string $resourceId): array
    {
        if (!isset($this->propertyObservers[ $context ], $this->properties[ $context ])) {
            return [];
        }

        $resource = [];

        foreach ($this->propertyObservers[ $context ] as $settingId => $observer) {
            $resource[ $settingId ] = $this->getProperty($settingId, $context, $resourceId);
        }

        return $resource;
    }

    /**
     * Retrieves the raw properties of a resource in the specified context.
     *
     * @param string $context    The context in which the resource exists.
     * @param string $resourceId The identifier of the resource to retrieve the properties for.
     *
     * @return array An array containing the raw properties of the specified resource in the specified context. If the
     *               context or the resource doesn't have any properties, or if the resource doesn't exist, an empty
     *               array is returned.
     */
    public function getResourcePropertiesRaw(string $context, string $resourceId): array
    {
        return $this->properties[ $context ][ $resourceId ] ?? [];
    }

    /**
     * Retrieves all the raw settings.
     *
     * @return array An array containing all the raw settings. This includes settings from all contexts without any
     *               modification or filtering.
     */
    public function getAllSettingsRaw(): array
    {
        return $this->settings;
    }

    /**
     * @deprecated This method is deprecated. Please use getAllSettingsRaw() instead.
     */
    public function getAllRaw(): array
    {
        trigger_error(
            'The method getAllRaw() is deprecated. Please use getAllSettingsRaw() instead.',
            E_USER_DEPRECATED
        );

        return $this->getAllSettingsRaw();
    }

    /**
     * Retrieves all the raw properties.
     *
     * @return array An array containing all the raw properties. This includes properties from all contexts without any
     *               modification or filtering.
     */
    public function getAllPropertiesRaw(): array
    {
        return $this->properties;
    }

    /**
     * Retrieves all the settings from all contexts.
     *
     * @return array An array containing all the settings from all contexts. The settings are organized in a nested
     *               array structure where the outer array represents the contexts, and the inner arrays contain the
     *               corresponding setting values.
     */
    public function getAllSettings(): array
    {
        $settings = [];
        foreach ($this->observers as $context => $observers) {
            foreach ($observers as $settingId => $observer) {
                $settings[ $context ][ $settingId ] = $this->get($settingId, $context);
            }
        }

        return $settings;
    }

    /**
     * @deprecated This method is deprecated. Please use getAllSettings() instead.
     */
    public function getAll(): array
    {
        trigger_error(
            'The method getAll() is deprecated. Please use getAllSettings() instead.',
            E_USER_DEPRECATED
        );

        return $this->getAllSettings();
    }

    /**
     * Retrieves all the properties from all contexts and resources.
     *
     * @return array An array containing all the properties from all contexts and resources. The properties are
     *               organized in a nested array structure where the outer array represents the contexts, the middle
     *               array represents the resources, and the inner arrays contain the corresponding property values.
     */
    public function getAllProperties(): array
    {
        $settings = [];
        foreach ($this->propertyObservers as $context => $observers) {
            foreach ($observers as $settingId => $observer) {
                $props = $this->properties[ $context ] ?? [];
                foreach ($props as $resourceId => $properties) {
                    $settings[ $context ][ $resourceId ][ $settingId ] = $this->getProperty($settingId, $context, $resourceId);
                }
            }
        }

        return $settings;
    }

    /**
     * Retrieves the value of a registered setting in the specified context.
     *
     * @param string $settingId The identifier of the setting to retrieve.
     * @param string $context   The context in which the setting exists. (default: self::CONTEXT_APP)
     *
     * @return mixed|null The value of the requested setting.
     *
     * @throws \UnexpectedValueException If the requested setting is not registered, or if it has an invalid value
     *                                   according to its associated observer class.
     */
    public function get(string $settingId, string $context = self::CONTEXT_APP)
    {
        if (!isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is not a registered setting.");
        }

        /** @var ObserverInterface $observerClass */
        $observerClass = $this->observers[ $context ][ $settingId ];
        $observerClass::onBeforeGet();
        $settingValue = $this->settings[ $context ][ $settingId ] ?? $observerClass::default();

        if (!$observerClass::validate($settingValue)) {
            throw new \UnexpectedValueException("$settingId has an invalid value: $settingValue");
        }

        $observerClass::onGet($settingValue);

        $resolvedValue = $this->resolveDependencies(
            $observerClass::sanitize($settingValue),
            $observerClass::dependencies()
        );

        return $observerClass::onGetFilter($resolvedValue);
    }

    /**
     * Retrieves the value of a registered property for a specific resource in the given context.
     *
     * @param string $settingId  The identifier of the property to retrieve.
     * @param string $context    The context in which the property exists.
     * @param string $resourceId The identifier of the resource.
     *
     * @return mixed|null The value of the requested property, or the default value associated with the property
     *                    observer class if the resource is not found.
     *
     * @throws \UnexpectedValueException If the requested property is not registered, or the property has an invalid
     *                                   value according to its associated property observer class.
     */
    public function getProperty(string $settingId, string $context, string $resourceId)
    {
        if (!isset($this->propertyObservers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is not a registered property.");
        }

        /** @var PropertyObserverInterface $observerClass */
        $observerClass = $this->propertyObservers[ $context ][ $settingId ];
        $observerClass::onBeforeGet($resourceId);

        $properties   = $this->properties[ $context ][ $resourceId ] ?? [];
        $settingValue = $properties[ $settingId ] ?? $observerClass::default($resourceId);

        if (!$observerClass::validate($settingValue, $resourceId)) {
            throw new \UnexpectedValueException("$settingId has an invalid value: $settingValue");
        }

        $observerClass::onGet($settingValue, $resourceId);

        $resolvedValue = $this->resolveDependencies(
            $observerClass::sanitize($settingValue, $resourceId),
            $observerClass::dependencies($resourceId)
        );

        return $observerClass::onGetFilter($resourceId, $resolvedValue);

    }

    /**
     * Saves the value of a registered setting for the given context.
     *
     * @param string $settingId The identifier of the setting to save.
     * @param mixed  $value     The value to save for the setting.
     * @param string $context   The context in which the setting exists.
     *
     * @return void
     *
     * @throws \UnexpectedValueException If the requested setting is not registered or the value is invalid according
     *                                   to its associated observer class.
     */
    public function save(string $settingId, $value, string $context = self::CONTEXT_APP): void
    {
        if (!isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("Setting '$settingId' is not registered in the '$context' context.");
        }

        /** @var ObserverInterface $observerClass */
        $observerClass = $this->observers[ $context ][ $settingId ];

        if (!$observerClass::validate($value)) {
            throw new \UnexpectedValueException("Invalid value provided for setting '$settingId' in the '$context' context.");
        }

        $observerClass::onSave($value);

        $this->settings[ $context ][ $settingId ] = $observerClass::sanitize($value);
    }

    /**
     * Saves the value of a registered property for the given context and resource.
     *
     * @param string $settingId  The identifier of the property to save.
     * @param mixed  $value      The value to save for the property.
     * @param string $context    The context in which the property exists.
     * @param string $resourceId The identifier of the resource associated with the property.
     *
     * @return void
     *
     * @throws \UnexpectedValueException If the requested property is not registered or the value is invalid according
     *                                   to its associated observer class.
     */
    public function saveProperty(string $settingId, $value, string $context, string $resourceId): void
    {
        if (!isset($this->propertyObservers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("Property '$settingId' is not a registered property in the '$context' context.");
        }

        /** @var PropertyObserverInterface $observerClass */
        $observerClass = $this->propertyObservers[ $context ][ $settingId ];

        if (!$observerClass::validate($value, $resourceId)) {
            throw new \UnexpectedValueException("Invalid value provided for property '$settingId' in the '$context' context.");
        }

        $observerClass::onSave($value, $resourceId);

        $this->properties[ $context ][ $resourceId ][ $settingId ] = $observerClass::sanitize($value, $resourceId);
    }

    /**
     * Resolves dependencies for a value based on specified conditions.
     *
     * This private method is responsible for resolving dependencies when querying a value, not during saving.
     *
     * @param mixed           $value        The value to be resolved for dependencies.
     * @param Dependency|null $dependencies The Dependency object representing the dependencies to be resolved.
     *
     * @return mixed|null The resolved value if all dependencies are satisfied based on the specified conditions, or
     *                    null if any condition fails.
     *
     * @throws \UnexpectedValueException If a registered setting is not found or has an invalid value according to its
     *                                   associated observer class.
     */
    private function resolveDependencies($value, ?Dependency $dependencies)
    {
        if (NULL === $dependencies) {
            return $value;
        }

        $pass = $dependencies->getGroupLogic() === 'AND';

        foreach ($dependencies->getConditions() as $condition) {

            if ($condition instanceof DependencyCondition) {
                $settingId = $condition->getSettingId();
                $context   = $condition->getContext();

                if (!isset($this->observers[ $context ][ $settingId ])) {
                    throw new \UnexpectedValueException("The setting '$settingId' is not registered in the '$context' context.");
                }

                /** @var ObserverInterface $observerClass */
                $observerClass = $this->observers[ $context ][ $settingId ];

                $settingValue = $this->settings[ $context ][ $settingId ] ?? $observerClass::default();

                if (!$observerClass::validate($settingValue)) {
                    throw new \UnexpectedValueException("The setting '$settingId' has an invalid value: $settingValue");
                }

                if ($dependencies->getGroupLogic() === 'AND' && !$condition->verified($settingValue)) {
                    $pass = FALSE;
                }
                if ($dependencies->getGroupLogic() === 'OR' && $condition->verified($settingValue)) {
                    $pass = TRUE;
                }
            } else {
                $settingValue = $this->resolveDependencies($value, $condition);
                if (NULL === $settingValue && $dependencies->getGroupLogic() === 'AND') {
                    $pass = FALSE;
                }
                if (NULL !== $settingValue && $dependencies->getGroupLogic() === 'OR') {
                    $pass = TRUE;
                }
            }

        }

        return $pass ? $value : NULL;
    }

}