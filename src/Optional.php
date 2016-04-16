<?php
namespace Optional;

/**
 * Class Optional
 *
 * @package Optional
 */
final class Optional
{
    /**
     * @var null|mixed
     */
    private $value = null;

    /**
     * Optional constructor.
     *
     * @param null $value
     */
    private function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Match the Java Optional<T>.empty() but as empty is a reserved keyword
     * for php version < 7.x we use blank instead
     *
     * @return Optional
     */
    public static function blank()
    {
        return new Optional();
    }

    /**
     * @param mixed $value
     *
     * @return Optional
     * @throws NullPointerException
     */
    public static function of($value)
    {
        if (is_null($value)) {
            throw new NullPointerException();
        }

        return new Optional($value);
    }

    /**
     * @param mixed $value
     *
     * @return Optional
     */
    public static function ofNullable($value)
    {
        return is_null($value) ? self::blank() : new Optional($value);
    }

    /**
     * @return mixed|null
     * @throws NoSuchElementException
     */
    public function get()
    {
        if (is_null($this->value)) {
            throw new NoSuchElementException();
        }

        return $this->value;
    }

    /**
     * @return boolean
     */
    public function isPresent()
    {
        return !is_null($this->value);
    }

    /**
     * @param callable $consumer
     */
    public function ifPresent(callable $consumer)
    {
        if ($this->isPresent()) {
            $consumer($this->value);
        }
    }

    /**
     * @param callable $predicate
     *
     * @return Optional
     */
    public function filter(callable $predicate)
    {
        if (!$this->isPresent()) {
            return $this;
        } else {
            return (bool) $predicate($this->value) ? $this : self::blank();
        }
    }

    /**
     * @param callable $mapper
     *
     * @return Optional
     */
    public function map(callable $mapper)
    {
        if (!$this->isPresent()) {
            return $this;
        } else {
            return self::ofNullable($mapper($this->value));
        }
    }

    /**
     * @param callable $mapper
     *
     * @return Optional
     * @throws NullPointerException
     */
    public function flatMap(callable $mapper)
    {
        if (!$this->isPresent()) {
            return $this;
        } else {
            $result = $mapper($this->value);
            if ($result instanceof Optional) {
                return $result->isPresent() ? self::of($result->get()) : self::of(null);
            } else {
                return self::of($result);
            }
        }
    }

    /**
     * @param mixed $other
     *
     * @return mixed
     */
    public function orElse($other)
    {
        return $this->isPresent() ? $this->value : $other;
    }

    /**
     * @param callable $supplier
     *
     * @return mixed
     */
    public function orElseGet(callable $supplier)
    {
        return $this->isPresent() ? $this->value : $supplier();
    }

    /**
     * @param \Exception $e
     *
     * @return mixed
     * @throws \Exception
     */
    public function orElseThrow(\Exception $e)
    {
        if ($this->isPresent()) {
            return $this->value;
        } else {
            throw $e;
        }
    }
}
