<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Action_hooks
// Instance of class
{
    public static $hooks_instance;
    public static $actions;
    public static $current_action;
    public static $run_actions;

    public static function instance()
    {
        if (!self::$hooks_instance) {
            self::$hooks_instance = new Action_hooks();
        }

        return self::$hooks_instance;
    }

    /**
     * Add Action
     *
     * Add a new hook trigger action
     *
     * @param mixed $name
     * @param mixed $function
     * @param mixed $priority
     */
    public function add_action($name, $function, $priority = 10)
    {
        if (is_string($function)) {
            // If we have already registered this action return true
            if (isset(self::$actions[$name][$priority][$function])) {
                return true;
            }
        } elseif (is_array($function)) {
            // Class
            if (isset(self::$actions[$name][$priority][get_class($function[0]).'-'.$function[1]])) {
                return true;
            }
        }
        /**
         * Allows us to iterate through multiple action hooks.
         */
        if (is_array($name)) {
            foreach ($name as $name) {
                // Store the action hook in the $hooks array
                if (is_string($function)) {
                    // Store the action hook in the $hooks array
                    self::$actions[$name][$priority][$function] = array(
                        "function" => $function
                    );
                } elseif (is_array($function)) {
                    self::$actions[$name][$priority][get_class($function[0]).'-'.$function[1]] = array(
                        "class" => $function[0],
                        "method" => $function[1]
                    );
                }
            }
        } else {
            if (is_string($function)) {
                // Store the action hook in the $hooks array
                self::$actions[$name][$priority][$function] = array(
                    "function" => $function
                );
            } elseif (is_array($function)) {
                self::$actions[$name][$priority][get_class($function[0]).'-'.$function[1]] = array(
                    "class" => $function[0],
                    "method" => $function[1]
                );
            }
        }

        return true;
    }

    /**
     * Do Action
     *
     * Trigger an action for a particular action hook
     *
     * @param mixed $name
     * @param mixed $arguments
     * @return mixed
     */
    public function do_action($name, $arguments = "")
    {
        // Oh, no you didn't. Are you trying to run an action hook that doesn't exist?
        if (!isset(self::$actions[$name])) {
            return $arguments;
        }

        // Set the current running hook to this
        self::$current_action = $name;
        // Key sort our action hooks
        ksort(self::$actions[$name]);
        foreach (self::$actions[$name] as $priority => $names) {
            if (is_array($names)) {
                foreach ($names as $name) {
                    if (isset($name['function'])) {
                        $return = call_user_func_array($name['function'], array(
                            &$arguments
                        ));
                        if ($return) {
                            $arguments = $return;
                        }
                        self::$run_actions[$name][$priority];
                    } else {
                        if (method_exists($name['class'], $name['method'])) {

                            $return = call_user_func_array(array($name['class'], $name['method']), array(
                                &$arguments
                            ));

                            if ($return) {
                                $arguments = $return;
                            }

                            self::$run_actions[get_class($name['class']).'-'.$name['method']][$priority];
                        }
                    }
                }
            }
        }
        self::$current_action = '';

        return $arguments;
    }

    /**
     * Remove Action
     *
     * Remove an action hook. No more needs to be said.
     *
     * @param mixed $name
     * @param mixed $function
     * @param mixed $priority
     */
    public function remove_action($name, $function, $priority = 10)
    {
        if(!is_array($function)){
        // If the action hook doesn't, just return true
        if (!isset(self::$actions[$name][$priority][$function])) {
            return true;
        }
        // Remove the action hook from our hooks array
        unset(self::$actions[$name][$priority][$function]);

        } else if(is_array($function)){
            if (!isset(self::$actions[$name][$priority][$function[0].'-'.$function[1]])) {
                return true;
            }
            // Remove the action hook from our hooks array
            unset(self::$actions[$name][$priority][$function[0].'-'.$function[1]]);
        }
    }

    /**
     * Current Action
     *
     * Get the currently running action hook
     *
     */
    public function current_action()
    {
        return self::$current_action;
    }

    /**
     * Has Run
     *
     * Check if a particular hook has been run
     *
     * @param mixed $hook
     * @param mixed $priority
     */
    public function has_run($action, $priority = 10)
    {
        if (isset(self::$actions[$action][$priority])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Action Exists
     *
     * Does a particular action hook even exist?
     *
     * @param mixed $name
     */
    public function action_exists($name)
    {
        if (isset(self::$actions[$name])) {
            return true;
        } else {
            return false;
        }
    }
}
/**
 * Add a new action hook
 *
 * @param mixed $name
 * @param mixed $function
 * @param mixed $priority
 */
function add_action($name, $function, $priority = 10)
{
    return Action_hooks::instance()->add_action($name, $function, $priority);
}
/**
 * Run an action
 *
 * @param mixed $name
 * @param mixed $arguments
 * @return mixed
 */
function do_action($name, $arguments = "")
{
    return Action_hooks::instance()->do_action($name, $arguments);
}
/**
 * Remove an action
 *
 * @param mixed $name
 * @param mixed $function
 * @param mixed $priority
 */
function remove_action($name, $function, $priority = 10)
{
    return Action_hooks::instance()->remove_action($name, $function, $priority);
}
/**
 * Check if an action exists
 *
 * @param mixed $name
 */
function action_exists($name)
{
    return Action_hooks::instance()->action_exists($name);
}
