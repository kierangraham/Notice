<?
/*
 * Register shutdown function to save notices.
 */
register_shutdown_function(array('notice', 'save'));

/*
 * Load notices from session
 */
Notice::load();