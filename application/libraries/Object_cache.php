<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Object_cache
{
    private $cache = array();

    public $cache_hits = 0;

    public $cache_misses = 0;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        return $this->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    public function __unset($name)
    {
        unset($this->$name);
    }

    public function add($key, $data, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if ($this->_exists($key, $group)) {
            return false;
        }

        return $this->set($key, $data, $group);
    }

    public function decr($key, $offset = 1, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (! $this->_exists($key, $group)) {
            return false;
        }

        if (! is_numeric($this->cache[ $group ][ $key ])) {
            $this->cache[ $group ][ $key ] = 0;
        }

        $offset = (int) $offset;

        $this->cache[ $group ][ $key ] -= $offset;

        if ($this->cache[ $group ][ $key ] < 0) {
            $this->cache[ $group ][ $key ] = 0;
        }

        return $this->cache[ $group ][ $key ];
    }

    public function delete($key, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (! $this->_exists($key, $group)) {
            return false;
        }

        unset($this->cache[$group][$key]);

        return true;
    }

    public function flush()
    {
        $this->cache = array();

        return true;
    }

    public function get($key, $group = 'default', &$found = null)
    {
        if (empty($group)) {
            $group = 'default';
        }

        if ($this->_exists($key, $group)) {
            $found = true;
            $this->cache_hits += 1;
            if (is_object($this->cache[$group][$key])) {
                return clone $this->cache[$group][$key];
            } else {
                return $this->cache[$group][$key];
            }
        }

        $found = false;
        $this->cache_misses += 1;

        return false;
    }

    public function incr($key, $offset = 1, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (! $this->_exists($key, $group)) {
            return false;
        }

        if (! is_numeric($this->cache[ $group ][ $key ])) {
            $this->cache[ $group ][ $key ] = 0;
        }

        $offset = (int) $offset;

        $this->cache[ $group ][ $key ] += $offset;

        if ($this->cache[ $group ][ $key ] < 0) {
            $this->cache[ $group ][ $key ] = 0;
        }

        return $this->cache[ $group ][ $key ];
    }

    public function replace($key, $data, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (! $this->_exists($key, $group)) {
            return false;
        }

        return $this->set($key, $data, $group);
    }

    public function set($key, $data, $group = 'default')
    {
        if (empty($group)) {
            $group = 'default';
        }

        if (is_object($data)) {
            $data = clone $data;
        }

        $this->cache[$group][$key] = $data;

        return true;
    }

    public function stats()
    {
        echo "<p>";
        echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
        echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
        echo "</p>";
    }

    protected function _exists($key, $group)
    {
        return isset($this->cache[ $group ]) && (isset($this->cache[ $group ][ $key ]) || array_key_exists($key, $this->cache[ $group ]));
    }
}
