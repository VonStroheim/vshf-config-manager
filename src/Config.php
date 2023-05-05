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
    private $observers = [];

    /**
     * @param array $hydrateApp
     */
    public function __construct(array $hydrateApp = [])
    {
        $this->settings[ self::CONTEXT_APP ] = $hydrateApp;
    }

    /**
     * @param array  $data
     * @param string $context
     *
     * @return void
     */
    public function hydrate(array $data, string $context = self::CONTEXT_APP): void
    {
        $this->settings[ $context ] = $data;
    }

    public function registerObserver($settingId, $observerClassName, $context = self::CONTEXT_APP): void
    {
        if (isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is already registered.");
        }

        $this->observers[ $context ][ $settingId ] = $observerClassName;
    }

    public function getAllByContext(string $context = self::CONTEXT_APP): array
    {
        return $this->settings[ $context ] ?? [];
    }

    public function getAll(): array
    {
        return $this->settings;
    }

    public function get(string $settingId, string $context = self::CONTEXT_APP)
    {
        if (!isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is not a registered setting.");
        }

        /** @var ObserverInterface $observerClass */
        $observerClass = $this->observers[ $context ][ $settingId ];

        if (!isset($this->settings[ $context ][ $settingId ])) {
            return $this->resolveDependencies($observerClass::default(), $observerClass::dependencies());
        }

        $settingValue = $this->settings[ $context ][ $settingId ];

        if (!$observerClass::validate($settingValue)) {
            throw new \UnexpectedValueException("$settingId has an invalid value: $settingValue");
        }

        $observerClass::onGet($settingValue);

        return $this->resolveDependencies(
            $observerClass::sanitize($settingValue),
            $observerClass::dependencies()
        );

    }

    public function save(string $settingId, $value, string $context = self::CONTEXT_APP): void
    {
        if (!isset($this->observers[ $context ][ $settingId ])) {
            throw new \UnexpectedValueException("$settingId is not a registered setting.");
        }

        /** @var ObserverInterface $observerClass */
        $observerClass = $this->observers[ $context ][ $settingId ];

        if (!$observerClass::validate($value)) {
            throw new \UnexpectedValueException("$settingId has an invalid value: $value");
        }

        $observerClass::onSave($value);

        $this->settings[ $context ][ $settingId ] = $observerClass::sanitize($value);
    }

    /**
     * Dependencies must be resolved only when querying, never when saving.
     *
     * @param mixed           $value
     * @param Dependency|null $dependencies
     *
     * @return mixed
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
                if (!isset($this->observers[ $condition->getContext() ][ $settingId ])) {
                    throw new \UnexpectedValueException("$settingId is not a registered setting.");
                }

                /** @var ObserverInterface $observerClass */
                $observerClass = $this->observers[ $condition->getContext() ][ $settingId ];

                $settingValue = $this->settings[ $condition->getContext() ][ $settingId ] ?? $observerClass::default();

                if (!$observerClass::validate($settingValue)) {
                    throw new \UnexpectedValueException("$settingId has an invalid value: $settingValue");
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