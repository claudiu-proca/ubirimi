<?php

use Ubirimi\Container\UbirimiContainer;
use Ubirimi\Yongo\Repository\Project\Project;
use Ubirimi\Repository\Client;
use Ubirimi\Util;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/bootstrap_cli.php';
require_once __DIR__. '/bugzilla/repository.php';
require_once __DIR__. '/bugzilla/connection.php';
require_once __DIR__ . '/bugzilla/bug_convertor.php';

/*
 * 1. install client record
 * 2. install products into projects
 * 3. install components per project
 * 4. install versions per project
 * 5. install users
 * 6. install bugs
 *      - install bugzilla priority
 *      - install bugzilla resolutions
 *      - install bugzilla statuses
 *      - install bugzilla severity
 *      - install bugzilla workflow
 *      - install bugs
 */

try {
    $clientData = array(
        'companyName' => 'Movidius',
        'companyDomain' => 'movidius',
        'baseURL' => 'http://movidius.ubirimi_net.lan',
        'companyEmail' => 'contact@movidius.ro',
        Client::INSTANCE_TYPE_ON_DEMAND,
        Util::getCurrentDateTime()
    );

    $valentinData = array(
        'firstName' => 'Vali',
        'lastName' => 'Muresan',
        'username' => 'vali',
        'password' => 'everythingisawesome',
        'email' => 'vali@vali.com'
    );

    UbirimiContainer::get()['db.connection']->autocommit(false);

    /* install client record Movidius -- start */
    $data = installMovidiusClient($clientData, $valentinData);

    $clientId = $data[0];
    $valiId = $data[1];
    /* install client record Movidius -- end */

    /* install products into projects -- start */
    $movidiusProjects = getProducts($connectionBugzilla);
    foreach ($movidiusProjects as &$product) {
        $projectId = installProject($clientId, $valiId, $product['name'], $product['description']);
        $product['yongo_project_id'] = $projectId;
    }
    /* install products into projects -- end */

    /* install components per project-- start */
    $movidiusComponents = getComponents($connectionBugzilla);
    foreach ($movidiusComponents as $component) {
        installComponent(
            $valiId,
            getYongoProjectFromMovidusProject($movidiusProjects, $component['product_id']),
            $component['name'],
            $component['description']
        );
    }
    /* install components per project-- end */

    /* install versions per project -- start */
    $movidiusVersions = getVersions($connectionBugzilla);
    foreach ($movidiusVersions as &$version) {
        installVersion(getYongoProjectFromMovidusProject($movidiusProjects, $version['product_id']), $version['value']);
    }
    /* install versions per project -- end */

    /* install users -- start */
    $movidiusUsers = getUsers($connectionBugzilla);
    foreach ($movidiusUsers as $movidiusUser) {
        $firstName = substr($movidiusUser['realname'], 0, strpos($movidiusUser['realname'], ' '));
        $lastName = substr($movidiusUser['realname'], strpos($movidiusUser['realname'], ' ') + 1);

        if (empty($movidiusUser['disabledtext'])) {
            installUser(array(
                'clientId' => $clientId,
                'username' => $movidiusUser['login_name'],
                'password' => 'everythingisawesome',
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $movidiusUser['login_name'],
                'clientDomain' => $clientData['companyDomain']
            ));
        }
    }
    /* install users -- end */

    /* install bugs -- start */
    installPriorities($connectionBugzilla, $clientId);

//    $movidiusBugs = getBugs($connectionBugzilla);
//    foreach ($movidiusBugs as $bug) {
//
//    }
    /* install bugs -- end */

    UbirimiContainer::get()['db.connection']->commit();
} catch (Exception $e) {
    UbirimiContainer::get()['db.connection']->rollback();
    echo $e->getMessage();
}

UbirimiContainer::get()['db.connection']->autocommit(true);