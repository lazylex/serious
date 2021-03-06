<?php

use yii\helpers\Html;
use backend\components\TreeBuilder\TreeBuilder;

$this->title = 'Редактировать: ' . $user['name'];
$this->params['breadcrumbs'][] = ['label' => 'RBAC', 'url' => 'index'];
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => 'users'];
$this->params['breadcrumbs'][] = $this->title;

$auth = \Yii::$app->authManager;

if (!(isset($roles_selector_type) && ($roles_selector_type == 'radio' || $roles_selector_type == 'checkbox'))) {
    $roles_selector_type = 'radio';
}

if (!\Yii::$app->user->can('changeAllRoles') && !\Yii::$app->user->can('changeRole', ['roles' => $user['roles']])) {
    \Yii::$app->session->setFlash('error', "У Вас нет прав для редактирования данного пользователя");
    return $this->redirect('users');
}


?>

<div style="background: white; border: solid 1px #e8e8e8; border-radius: 5px; padding: 5px">
    <form method="post" action="<?= \yii\helpers\Url::to(['/rbac/user']) ?>">'
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group-item list-group-item-warning">Пользователь <?= $user['name'] ?></div>

                <?php

                $treeBuilder = new TreeBuilder();

                foreach ($user['roles'] as $userRole) {

                    $treeBuilder->auth_item = $as->getAuthItem();
                    $treeBuilder->tree = $as->getTree($userRole['role']);
                    /* заполняем разрешения, принадлежащие непосредственно роли, а не ее наследникам */
                    if (isset($treeBuilder->tree['roles'][$userRole['role']]['permissions']))
                        foreach ($treeBuilder->tree['roles'][$userRole['role']]['permissions'] as $key => $originalPermission) {
                            $userOriginalPermissions[] = $key;
                        }
                }


                foreach ($userPrivatePermissions as $permission) {
                    if ($as->isPermission($permission)) {
                        $treeBuilder->tree = $as->getTree($permission);
                    }
                }
                echo $treeBuilder->buildList($treeBuilder->tree);
                ?>

            </div>
            <div class="col-md-5">

                <!-- Вывод таблицы разрешений -->
                <div class="list-group-item list-group-item-warning">Разрешения пользователя <?= $user['name'] ?></div>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <th>Личные разрешения пользователя</th>
                    <th>Разрешения от своих ролей</th>
                    <th>Разрешения, полученные с наследуемыми ролями</th>
                    <th>Название разрешения</th>
                    <th>Описание разрешения</th>
                    <th>Правило</th>
                    </thead>

                    <?php foreach ($allPermissions as $permission_name) :

                        if ($permission_name == 'changeAllRoles' && (!\Yii::$app->user->can('changeAllRoles')))
                            continue;
                        ?>
                        <tr id="tr_<?= $permission_name ?>" <?= in_array($permission_name, $userPermissions)
                        || in_array($permission_name, $userOriginalPermissions)
                        || in_array($permission_name, $userPrivatePermissions) ? 'style="background: #d9edf7"' : '' ?>>
                            <td>
                                <input name="private_permissions[]"
                                       type="checkbox"
                                       value="<?= $permission_name ?>"
                                    <?= in_array($permission_name, $userPrivatePermissions) ? 'checked="checked"' : '' ?>
                                    <?= ($permission_name=='changeRole'||$permission_name=='createRole')
                                    &&!\Yii::$app->user->can('changeAllRoles')?'disabled="disabled"':''?>
                                >
                            </td>
                            <td>
                                <input type="checkbox" disabled="disabled"
                                    <?= in_array($permission_name, $userOriginalPermissions) ? 'checked="checked"' : '' ?>>
                            </td>
                            <td>
                                <input type="checkbox" disabled="disabled"
                                    <?= in_array($permission_name, $userPermissions)
                                    && !in_array($permission_name, $userOriginalPermissions)
                                    && !in_array($permission_name, $userPrivatePermissions) ? 'checked="checked"' : '' ?>>
                            </td>
                            <td><?= $permission_name ?></td>
                            <td><?= $as->getItemDescription($permission_name) ?></td>
                            <td><?= $as->getItemRule($permission_name) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="col-md-4">
                <div class="list-group-item list-group-item-warning">Роли пользователя <?= $user['name'] ?></div>
                <!--Вывод таблицы ролей-->
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <th>Активно</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Правило</th>
                    </thead>

                    <?php foreach ($allRoles as $role_name) : ?>
                        <tr>
                            <td>
                                <input name="roles[]"
                                       value="<?= $role_name ?>"
                                       type="<?= $roles_selector_type ?>"
                                    <?= in_array($role_name, $userRoles) ? 'checked="checked"' : '' ?>
                                    <?php if (($role_name == 'Главный'||$role_name == 'Заместитель') && (!\Yii::$app->user->can('changeAllRoles')))
                                        echo 'disabled="disabled"';
                                    ?>
                                >
                            </td>
                            <td><?= $role_name ?></td>
                            <td><?= $as->getItemDescription($role_name) ?></td>
                            <td><?= $as->getItemRule($role_name) ?></td>
                        </tr>
                    <?php endforeach; ?>



                </table>
            </div>
        </div>
        <div style="text-align: center">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
        <input type="hidden" name="_csrf-backend" value="<?= Yii::$app->request->getCsrfToken() ?>">
    </form>
</div>