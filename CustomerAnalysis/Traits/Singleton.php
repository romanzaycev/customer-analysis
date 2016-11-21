<?php

namespace CustomerAnalysis\Traits;

/**
 * Trait Singleton
 * @package CustomerAnalysis\Traits
 */
trait Singleton
{

    protected static $instance = null;

    /**
     * @return static
     */
    final public static function getInstance()
    {
        return (static::$instance !== null)
            ? static::$instance
            : static::$instance = new static;
    }

    /**
     * Singleton constructor.
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     * Singleton initialization method.
     */
    protected function init()
    {
    }

    /**
     * Protected magic wakeup method.
     */
    final private function __wakeup()
    {
    }

    /**
     * Protected magic clone method.
     */
    final private function __clone()
    {
    }

}

// EOF Singleton.php