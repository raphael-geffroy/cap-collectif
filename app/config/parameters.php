<?php

function get_env_var($key) {
    return getenv('SYMFONY_'.strtoupper(str_replace('.', '__', $key)));
}

function set_var($key, $default) {
    return get_env_var($key) ?: $default;
}

$container->setParameter('database_driver',             set_var('database_driver', 'pdo_mysql'));
$container->setParameter('database_host',               set_var('database_host', '127.0.0.1'));
$container->setParameter('database_port',               set_var('database_port', 3306));
$container->setParameter('database_name',               set_var('database_name', 'symfony'));
$container->setParameter('database_user',               set_var('database_user', 'root'));
$container->setParameter('database_password',           set_var('database_password', null));

$container->setParameter('mailer_transport',            set_var('mailer_transport', 'smtp'));
$container->setParameter('mailer_user',                 set_var('mailer_user', '***REMOVED***'));
$container->setParameter('mailer_password',             set_var('mailer_password', '***REMOVED***'));
$container->setParameter('mailer_host',                 set_var('mailer_host', 'in-v3.mailjet.com'));
$container->setParameter('mailer_port',                 set_var('mailer_port', 587));

$container->setParameter('mailer_contact_user',         set_var('mailer_contact_user', 'maxime@cap-collectif.com'));
$container->setParameter('mailer_contact_password',     set_var('mailer_contact_password', '***REMOVED***'));
$container->setParameter('mailer_contact_host',         set_var('mailer_contact_host', '***REMOVED***'));

$container->setParameter('locale',                      set_var('locale', 'fr'));
$container->setParameter('secret',                      set_var('secret', '***REMOVED***'));
$container->setParameter('debug_toolbar',               set_var('debug_toolbar', true));
$container->setParameter('debug_redirects',             set_var('debug_redirects', false));
$container->setParameter('use_assetic_controller',      set_var('use_assetic_controller', true));

$container->setParameter('facebook_app_id',             set_var('facebook_app_id', '***REMOVED***'));
$container->setParameter('facebook_app_secret',         set_var('facebook_app_secret', '***REMOVED***'));
$container->setParameter('google_app_id',               set_var('google_app_id', '***REMOVED***'));
$container->setParameter('google_app_secret',           set_var('google_app_secret', '***REMOVED***'));
$container->setParameter('twitter_app_id',              set_var('twitter_app_id', '***REMOVED***'));
$container->setParameter('twitter_app_secret',          set_var('twitter_app_secret', '***REMOVED***'));

$container->setParameter('redis_prefix',                set_var('redis_prefix', 'capco'));
$container->setParameter('shield_login',                set_var('shield_login', '***REMOVED***'));
$container->setParameter('shield_pwd',                  set_var('shield_pwd', '***REMOVED***'));

$container->setParameter('base.url',                    set_var('base.url', 'http://127.0.0.1:8000/'));

$container->setParameter('jwt_private_key_path',        set_var('jwt_private_key_path', '%kernel.root_dir%/var/jwt/private.pem'));
$container->setParameter('jwt_public_key_path',         set_var('jwt_public_key_path', '%kernel.root_dir%/var/jwt/public.pem'));
$container->setParameter('jwt_key_pass_phrase',         set_var('jwt_key_pass_phrase', 'iamapassphrase'));
$container->setParameter('jwt_token_ttl',               set_var('jwt_token_ttl', 86400));

$container->setParameter('language_analyzer',           set_var('language_analyzer', 'french'));

$container->setParameter('remember_secret',           set_var('remember_secret', '***REMOVED***'));

if (file_exists('app/config/parameters.yml') || file_exists('../app/config/parameters.yml')) {
    $loader->import('parameters.yml');
}
