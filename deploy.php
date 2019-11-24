<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Project name
// TODO: CHANGE_ME set('application', 'CHANGE_ME');
set('application', 'skeleton');

set(
    'bin/php', function () {
        return '/opt/php73/bin/php';
    }
);

// composer fix metanet
set(
    'bin/composer', function () {
        return 'export PATH=/opt/php73/bin:$PATH && {{bin/php}} /usr/lib64/plesk-9.0/composer.phar';
    }
);

// Project repository
// TODO: CHANGE_ME set('repository', 'git@gitlab.com:JKwebGmbH/silkeschaefer-community.git');
set('repository', 'git@gitlab.com:JKwebGmbH/symfony4-skeleton.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_dirs', ['var/log', 'var/sessions', 'data']);

task(
    'deploy:frontend:build', function () {
        runLocally('yarn install');
        runLocally('yarn run encore production');
        runLocally('rsync -azP -e "ssh -p 2121" public/build {{user}}@{{hostname}}:{{release_path}}/public');
    }
)->desc('Build frontend assets');

inventory('servers.yaml');

// Hosts
set('default_stage', 'dev');
set('writable_use_sudo', false);
set('ssh_multiplexing', false);

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');

desc('Migrate database');
task('database:migrate', function () {
    run('{{bin/console}} doctrine:migrations:migrate --allow-no-migration');
});

desc('Deploy project');
task(
    'deploy', [
        'deploy:info',
        'deploy:prepare',
        'deploy:lock',
        'deploy:release',
        'deploy:update_code',
        'deploy:shared',
        'deploy:vendors',
        'database:migrate',
        'deploy:frontend:build',
        'deploy:writable',
        'deploy:cache:clear',
        'deploy:cache:warmup',
        'deploy:symlink',
        'deploy:unlock',
        'cleanup',
    ]
);
