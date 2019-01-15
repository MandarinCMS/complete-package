<?php
/**
 * The module API is located in this file, which allows for creating actions
 * and filters and hooking functions, and methods. The functions or methods will
 * then be run when the action or filter is called.
 *
 * The API callback examples reference functions, but can be methods of classes.
 * To hook methods, you'll need to pass an array one of two ways.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link https://secure.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * Also see the {@link https://dev.mandarincms.com/Module_API Module API} for
 * more information and examples on how to use a lot of these functions.
 *
 * This file should have no external dependencies.
 *
 * @package MandarinCMS
 * @subpackage Module
 * @since 1.5.0
 */

// Initialize the filter globals.
require( dirname( __FILE__ ) . '/class-mcms-hook.php' );

/** @var MCMS_Hook[] $mcms_filter */
global $mcms_filter, $mcms_actions, $mcms_current_filter;

if ( $mcms_filter ) {
	$mcms_filter = MCMS_Hook::build_preinitialized_hooks( $mcms_filter );
} else {
	$mcms_filter = array();
}

if ( ! isset( $mcms_actions ) )
	$mcms_actions = array();

if ( ! isset( $mcms_current_filter ) )
	$mcms_current_filter = array();

/**
 * Hook a function or method to a specific filter action.
 *
 * MandarinCMS offers filter hooks to allow modules to modify
 * various types of internal data at runtime.
 *
 * A module can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *         return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Bound callbacks can accept from none to the total number of arguments passed as parameters
 * in the corresponding apply_filters() call.
 *
 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
 * opted to accept. If no arguments were accepted by the callback that is considered to be the
 * same as accepting 1 argument. For example:
 *
 *     // Filter call.
 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
 *
 *     // Accepting zero/one arguments.
 *     function example_callback() {
 *         ...
 *         return 'some value';
 *     }
 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
 *
 *     // Accepting two arguments (three possible).
 *     function example_callback( $value, $arg2 ) {
 *         ...
 *         return $maybe_modified_value;
 *     }
 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
 *
 * *Note:* The function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes, so
 * everything is as quick as possible.
 *
 * @since 0.71
 *
 * @global array $mcms_filter      A multidimensional array of all hooks and the callbacks hooked to them.
 *
 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
 * @param callable $function_to_add The callback to be run when the filter is applied.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	global $mcms_filter;
	if ( ! isset( $mcms_filter[ $tag ] ) ) {
		$mcms_filter[ $tag ] = new MCMS_Hook();
	}
	$mcms_filter[ $tag ]->add_filter( $tag, $function_to_add, $priority, $accepted_args );
	return true;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @global array $mcms_filter Stores all of the filters.
 *
 * @param string        $tag               The name of the filter hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                   anything registered. When checking a specific function, the priority of that
 *                   hook is returned, or false if the function is not attached. When using the
 *                   $function_to_check argument, this function may return a non-boolean value
 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                   return value.
 */
function has_filter($tag, $function_to_check = false) {
	global $mcms_filter;

	if ( ! isset( $mcms_filter[ $tag ] ) ) {
		return false;
	}

	return $mcms_filter[ $tag ]->has_filter( $tag, $function_to_check );
}

/**
 * Call the functions added to a filter hook.
 *
 * The callback functions attached to filter hook $tag are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the $tag parameter.
 *
 * The function allows for additional arguments to be added and passed to hooks.
 *
 *     // Our filter callback function
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback' function we
 *      * "hooked" to 'example_filter' using the add_filter() function above.
 *      * - 'example_filter' is the filter hook $tag
 *      * - 'filter me' is the value being filtered
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global array $mcms_filter         Stores all of the filters.
 * @global array $mcms_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $tag     The name of the filter hook.
 * @param mixed  $value   The value on which the filters hooked to `$tag` are applied on.
 * @param mixed  $var,... Additional variables passed to the functions hooked to `$tag`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $tag, $value ) {
	global $mcms_filter, $mcms_current_filter;

	$args = array();

	// Do 'all' actions first.
	if ( isset($mcms_filter['all']) ) {
		$mcms_current_filter[] = $tag;
		$args = func_get_args();
		_mcms_call_all_hook($args);
	}

	if ( !isset($mcms_filter[$tag]) ) {
		if ( isset($mcms_filter['all']) )
			array_pop($mcms_current_filter);
		return $value;
	}

	if ( !isset($mcms_filter['all']) )
		$mcms_current_filter[] = $tag;

	if ( empty($args) )
		$args = func_get_args();

	// don't pass the tag name to MCMS_Hook
	array_shift( $args );

	$filtered = $mcms_filter[ $tag ]->apply_filters( $value, $args );

	array_pop( $mcms_current_filter );

	return $filtered;
}

/**
 * Execute functions hooked on a specific filter hook, specifying arguments in an array.
 *
 * @since 3.0.0
 *
 * @see apply_filters() This function is identical, but the arguments passed to the
 * functions hooked to `$tag` are supplied using an array.
 *
 * @global array $mcms_filter         Stores all of the filters
 * @global array $mcms_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the filter hook.
 * @param array  $args The arguments supplied to the functions hooked to $tag.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters_ref_array($tag, $args) {
	global $mcms_filter, $mcms_current_filter;

	// Do 'all' actions first
	if ( isset($mcms_filter['all']) ) {
		$mcms_current_filter[] = $tag;
		$all_args = func_get_args();
		_mcms_call_all_hook($all_args);
	}

	if ( !isset($mcms_filter[$tag]) ) {
		if ( isset($mcms_filter['all']) )
			array_pop($mcms_current_filter);
		return $args[0];
	}

	if ( !isset($mcms_filter['all']) )
		$mcms_current_filter[] = $tag;

	$filtered = $mcms_filter[ $tag ]->apply_filters( $args[0], $args );

	array_pop( $mcms_current_filter );

	return $filtered;
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @global array $mcms_filter         Stores all of the filters
 *
 * @param string   $tag                The filter hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool    Whether the function existed before it was removed.
 */
function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
	global $mcms_filter;

	$r = false;
	if ( isset( $mcms_filter[ $tag ] ) ) {
		$r = $mcms_filter[ $tag ]->remove_filter( $tag, $function_to_remove, $priority );
		if ( ! $mcms_filter[ $tag ]->callbacks ) {
			unset( $mcms_filter[ $tag ] );
		}
	}

	return $r;
}

/**
 * Remove all of the hooks from a filter.
 *
 * @since 2.7.0
 *
 * @global array $mcms_filter  Stores all of the filters
 *
 * @param string   $tag      The filter to remove hooks from.
 * @param int|bool $priority Optional. The priority number to remove. Default false.
 * @return true True when finished.
 */
function remove_all_filters( $tag, $priority = false ) {
	global $mcms_filter;

	if ( isset( $mcms_filter[ $tag ]) ) {
		$mcms_filter[ $tag ]->remove_all_filters( $priority );
		if ( ! $mcms_filter[ $tag ]->has_filters() ) {
			unset( $mcms_filter[ $tag ] );
		}
	}

	return true;
}

/**
 * Retrieve the name of the current filter or action.
 *
 * @since 2.5.0
 *
 * @global array $mcms_current_filter Stores the list of current filters with the current one last
 *
 * @return string Hook name of the current filter or action.
 */
function current_filter() {
	global $mcms_current_filter;
	return end( $mcms_current_filter );
}

/**
 * Retrieve the name of the current action.
 *
 * @since 3.9.0
 *
 * @return string Hook name of the current action.
 */
function current_action() {
	return current_filter();
}

/**
 * Retrieve the name of a filter currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being
 * executed (despite not being the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 * @see did_action()
 * @global array $mcms_current_filter Current filter.
 *
 * @param null|string $filter Optional. Filter to check. Defaults to null, which
 *                            checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 */
function doing_filter( $filter = null ) {
	global $mcms_current_filter;

	if ( null === $filter ) {
		return ! empty( $mcms_current_filter );
	}

	return in_array( $filter, $mcms_current_filter );
}

/**
 * Retrieve the name of an action currently being processed.
 *
 * @since 3.9.0
 *
 * @param string|null $action Optional. Action to check. Defaults to null, which checks
 *                            if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 */
function doing_action( $action = null ) {
	return doing_filter( $action );
}

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the MandarinCMS core launches at specific points
 * during execution, or when specific events occur. Modules can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @since 1.2.0
 *
 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
 * @param callable $function_to_add The name of the function you wish to be called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Will always return true.
 */
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	return add_filter($tag, $function_to_add, $priority, $accepted_args);
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook `$tag`. It is
 * possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$tag` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with apply_filters().
 *
 * @since 1.2.0
 *
 * @global array $mcms_filter         Stores all of the filters
 * @global array $mcms_actions        Increments the amount of times action was triggered.
 * @global array $mcms_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag     The name of the action to be executed.
 * @param mixed  $arg,... Optional. Additional arguments which are passed on to the
 *                        functions hooked to the action. Default empty.
 */
function do_action($tag, $arg = '') {
	global $mcms_filter, $mcms_actions, $mcms_current_filter;

	if ( ! isset($mcms_actions[$tag]) )
		$mcms_actions[$tag] = 1;
	else
		++$mcms_actions[$tag];

	// Do 'all' actions first
	if ( isset($mcms_filter['all']) ) {
		$mcms_current_filter[] = $tag;
		$all_args = func_get_args();
		_mcms_call_all_hook($all_args);
	}

	if ( !isset($mcms_filter[$tag]) ) {
		if ( isset($mcms_filter['all']) )
			array_pop($mcms_current_filter);
		return;
	}

	if ( !isset($mcms_filter['all']) )
		$mcms_current_filter[] = $tag;

	$args = array();
	if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
		$args[] =& $arg[0];
	else
		$args[] = $arg;
	for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
		$args[] = func_get_arg($a);

	$mcms_filter[ $tag ]->do_action( $args );

	array_pop($mcms_current_filter);
}

/**
 * Retrieve the number of times an action is fired.
 *
 * @since 2.1.0
 *
 * @global array $mcms_actions Increments the amount of times action was triggered.
 *
 * @param string $tag The name of the action hook.
 * @return int The number of times action hook $tag is fired.
 */
function did_action($tag) {
	global $mcms_actions;

	if ( ! isset( $mcms_actions[ $tag ] ) )
		return 0;

	return $mcms_actions[$tag];
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @since 2.1.0
 *
 * @see do_action() This function is identical, but the arguments passed to the
 *                  functions hooked to $tag< are supplied using an array.
 * @global array $mcms_filter         Stores all of the filters
 * @global array $mcms_actions        Increments the amount of times action was triggered.
 * @global array $mcms_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the action to be executed.
 * @param array  $args The arguments supplied to the functions hooked to `$tag`.
 */
function do_action_ref_array($tag, $args) {
	global $mcms_filter, $mcms_actions, $mcms_current_filter;

	if ( ! isset($mcms_actions[$tag]) )
		$mcms_actions[$tag] = 1;
	else
		++$mcms_actions[$tag];

	// Do 'all' actions first
	if ( isset($mcms_filter['all']) ) {
		$mcms_current_filter[] = $tag;
		$all_args = func_get_args();
		_mcms_call_all_hook($all_args);
	}

	if ( !isset($mcms_filter[$tag]) ) {
		if ( isset($mcms_filter['all']) )
			array_pop($mcms_current_filter);
		return;
	}

	if ( !isset($mcms_filter['all']) )
		$mcms_current_filter[] = $tag;

	$mcms_filter[ $tag ]->do_action( $args );

	array_pop($mcms_current_filter);
}

/**
 * Check if any action has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string        $tag               The name of the action hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority of that
 *                  hook is returned, or false if the function is not attached. When using the
 *                  $function_to_check argument, this function may return a non-boolean value
 *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                  return value.
 */
function has_action($tag, $function_to_check = false) {
	return has_filter($tag, $function_to_check);
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * @since 1.2.0
 *
 * @param string   $tag                The action hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $tag, $function_to_remove, $priority = 10 ) {
	return remove_filter( $tag, $function_to_remove, $priority );
}

/**
 * Remove all of the hooks from an action.
 *
 * @since 2.7.0
 *
 * @param string   $tag      The action to remove hooks from.
 * @param int|bool $priority The priority number to remove them from. Default false.
 * @return true True when finished.
 */
function remove_all_actions($tag, $priority = false) {
	return remove_all_filters($tag, $priority);
}

/**
 * Fires functions attached to a deprecated filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * Note: the value and extra arguments passed to the original apply_filters() call
 * must be passed here to `$args` as an array. For example:
 *
 *     // Old filter.
 *     return apply_filters( 'mcmsdocs_filter', $value, $extra_arg );
 *
 *     // Deprecated.
 *     return apply_filters_deprecated( 'mcmsdocs_filter', array( $value, $extra_arg ), '4.9', 'mcmsdocs_new_filter' );
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $tag         The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of MandarinCMS that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default false.
 * @param string $message     Optional. A message regarding the change. Default null.
 */
function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_filter( $tag ) ) {
		return $args[0];
	}

	_deprecated_hook( $tag, $version, $replacement, $message );

	return apply_filters_ref_array( $tag, $args );
}

/**
 * Fires functions attached to a deprecated action hook.
 *
 * When an action hook is deprecated, the do_action() call is replaced with
 * do_action_deprecated(), which triggers a deprecation notice and then fires
 * the original hook.
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $tag         The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of MandarinCMS that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
	if ( ! has_action( $tag ) ) {
		return;
	}

	_deprecated_hook( $tag, $version, $replacement, $message );

	do_action_ref_array( $tag, $args );
}

//
// Functions for handling modules.
//

/**
 * Gets the basename of a module.
 *
 * This method extracts the name of a module from its filename.
 *
 * @since 1.5.0
 *
 * @global array $mcms_module_paths
 *
 * @param string $file The filename of module.
 * @return string The name of a module.
 */
function module_basename( $file ) {
	global $mcms_module_paths;

	// $mcms_module_paths contains normalized paths.
	$file = mcms_normalize_path( $file );

	arsort( $mcms_module_paths );
	foreach ( $mcms_module_paths as $dir => $realdir ) {
		if ( strpos( $file, $realdir ) === 0 ) {
			$file = $dir . substr( $file, strlen( $realdir ) );
		}
	}

	$module_dir = mcms_normalize_path( MCMS_PLUGIN_DIR );
	$mu_module_dir = mcms_normalize_path( MCMSMU_PLUGIN_DIR );

	$file = preg_replace('#^' . preg_quote($module_dir, '#') . '/|^' . preg_quote($mu_module_dir, '#') . '/#','',$file); // get relative path from modules dir
	$file = trim($file, '/');
	return $file;
}

/**
 * Register a module's real path.
 *
 * This is used in module_basename() to resolve symlinked paths.
 *
 * @since 3.9.0
 *
 * @see mcms_normalize_path()
 *
 * @global array $mcms_module_paths
 *
 * @staticvar string $mcms_module_path
 * @staticvar string $mcmsmu_module_path
 *
 * @param string $file Known path to the file.
 * @return bool Whether the path was able to be registered.
 */
function mcms_register_module_realpath( $file ) {
	global $mcms_module_paths;

	// Normalize, but store as static to avoid recalculation of a constant value
	static $mcms_module_path = null, $mcmsmu_module_path = null;
	if ( ! isset( $mcms_module_path ) ) {
		$mcms_module_path   = mcms_normalize_path( MCMS_PLUGIN_DIR   );
		$mcmsmu_module_path = mcms_normalize_path( MCMSMU_PLUGIN_DIR );
	}

	$module_path = mcms_normalize_path( dirname( $file ) );
	$module_realpath = mcms_normalize_path( dirname( realpath( $file ) ) );

	if ( $module_path === $mcms_module_path || $module_path === $mcmsmu_module_path ) {
		return false;
	}

	if ( $module_path !== $module_realpath ) {
		$mcms_module_paths[ $module_path ] = $module_realpath;
	}

	return true;
}

/**
 * Get the filesystem directory path (with trailing slash) for the module __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the module (__FILE__).
 * @return string the filesystem path of the directory that contains the module.
 */
function module_dir_path( $file ) {
	return trailingslashit( dirname( $file ) );
}

/**
 * Get the URL directory path (with trailing slash) for the module __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the module (__FILE__).
 * @return string the URL path of the directory that contains the module.
 */
function module_dir_url( $file ) {
	return trailingslashit( modules_url( '', $file ) );
}

/**
 * Set the activation hook for a module.
 *
 * When a module is activated, the action 'activate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the module, including the optional subdirectory. For example, when the
 * module is located in mcms-plugins/modules/samplemodule/sample.php, then
 * the name of this hook will become 'activate_samplemodule/sample.php'.
 *
 * When the module consists of only one file and is (as by default) located at
 * mcms-plugins/modules/sample.php the name of this hook will be
 * 'activate_sample.php'.
 *
 * @since 2.0.0
 *
 * @param string   $file     The filename of the module including the path.
 * @param callable $function The function hooked to the 'activate_PLUGIN' action.
 */
function register_activation_hook($file, $function) {
	$file = module_basename($file);
	add_action('activate_' . $file, $function);
}

/**
 * Set the deactivation hook for a module.
 *
 * When a module is deactivated, the action 'deactivate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the module, including the optional subdirectory. For example, when the
 * module is located in mcms-plugins/modules/samplemodule/sample.php, then
 * the name of this hook will become 'deactivate_samplemodule/sample.php'.
 *
 * When the module consists of only one file and is (as by default) located at
 * mcms-plugins/modules/sample.php the name of this hook will be
 * 'deactivate_sample.php'.
 *
 * @since 2.0.0
 *
 * @param string   $file     The filename of the module including the path.
 * @param callable $function The function hooked to the 'deactivate_PLUGIN' action.
 */
function register_deactivation_hook($file, $function) {
	$file = module_basename($file);
	add_action('deactivate_' . $file, $function);
}

/**
 * Set the uninstallation hook for a module.
 *
 * Registers the uninstall hook that will be called when the user clicks on the
 * uninstall link that calls for the module to uninstall itself. The link won't
 * be active unless the module hooks into the action.
 *
 * The module should not run arbitrary code outside of functions, when
 * registering the uninstall hook. In order to run using the hook, the module
 * will have to be included, which means that any code laying outside of a
 * function will be run during the uninstallation process. The module should not
 * hinder the uninstallation process.
 *
 * If the module can not be written without running code within the module, then
 * the module should create a file named 'uninstall.php' in the base module
 * folder. This file will be called, if it exists, during the uninstallation process
 * bypassing the uninstall hook. The module, when using the 'uninstall.php'
 * should always check for the 'MCMS_UNINSTALL_PLUGIN' constant, before
 * executing.
 *
 * @since 2.7.0
 *
 * @param string   $file     Module file.
 * @param callable $callback The callback to run when the hook is called. Must be
 *                           a static method or function.
 */
function register_uninstall_hook( $file, $callback ) {
	if ( is_array( $callback ) && is_object( $callback[0] ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Only a static class method or function can be used in an uninstall hook.' ), '3.1.0' );
		return;
	}

	/*
	 * The option should not be autoloaded, because it is not needed in most
	 * cases. Emphasis should be put on using the 'uninstall.php' way of
	 * uninstalling the module.
	 */
	$uninstallable_modules = (array) get_option('uninstall_modules');
	$uninstallable_modules[module_basename($file)] = $callback;

	update_option('uninstall_modules', $uninstallable_modules);
}

/**
 * Call the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $mcms_filter  Stores all of the filters
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _mcms_call_all_hook($args) {
	global $mcms_filter;

	$mcms_filter['all']->do_all_hook( $args );
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $mcms_filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.mandarincms.com/ticket/3875
 *
 * @since 2.2.3
 * @access private
 *
 * @global array $mcms_filter Storage for all of the filters and actions.
 * @staticvar int $filter_id_count
 *
 * @param string   $tag      Used in counting how many hooks were applied
 * @param callable $function Used for creating unique id
 * @param int|bool $priority Used in counting how many hooks were applied. If === false
 *                           and $function is an object reference, we return the unique
 *                           id only if it already has one, false otherwise.
 * @return string|false Unique ID for usage as array key or false if $priority === false
 *                      and $function is an object reference, and it does not already have
 *                      a unique id.
 */
function _mcms_filter_build_unique_id($tag, $function, $priority) {
	global $mcms_filter;
	static $filter_id_count = 0;

	if ( is_string($function) )
		return $function;

	if ( is_object($function) ) {
		// Closures are currently implemented as objects
		$function = array( $function, '' );
	} else {
		$function = (array) $function;
	}

	if (is_object($function[0]) ) {
		// Object Class Calling
		if ( function_exists('spl_object_hash') ) {
			return spl_object_hash($function[0]) . $function[1];
		} else {
			$obj_idx = get_class($function[0]).$function[1];
			if ( !isset($function[0]->mcms_filter_id) ) {
				if ( false === $priority )
					return false;
				$obj_idx .= isset($mcms_filter[$tag][$priority]) ? count((array)$mcms_filter[$tag][$priority]) : $filter_id_count;
				$function[0]->mcms_filter_id = $filter_id_count;
				++$filter_id_count;
			} else {
				$obj_idx .= $function[0]->mcms_filter_id;
			}

			return $obj_idx;
		}
	} elseif ( is_string( $function[0] ) ) {
		// Static Calling
		return $function[0] . '::' . $function[1];
	}
}
