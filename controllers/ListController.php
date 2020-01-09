<?php

namespace wdmg\amp\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ListController implements the CRUD actions
 */
class ListController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
    }

    /**
     * Lists all AMP pages.
     * @return mixed
     */
    public function actionIndex()
    {
        $module = $this->module;
        $dataProvider = new ArrayDataProvider([
            'allModels' => $module->getAmpItems()
        ]);
        return $this->render('index', [
            'module' => $module,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Clear AMP pages cache
     *
     * @return mixed
     */
    public function actionClear()
    {
        if ($cache = Yii::$app->getCache()) {
            if ($cache->delete(md5('google-amp'))) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t('app/modules/amp', 'AMP pages cache has been successfully flushing!')
                );
            } else {
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t('app/modules/amp', 'An error occurred while flushing the AMP pages cache.')
                );
            }
        } else {
            Yii::$app->getSession()->setFlash(
                'warning',
                Yii::t('app/modules/amp', 'Error! Cache component not configured in the application.')
            );
        }

        return $this->redirect(['list/index']);
    }
}
