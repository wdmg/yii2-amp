<?php

namespace wdmg\amp\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use yii\web\View;

/**
 * DefaultController implements actions for AMP pages
 */
class DefaultController extends Controller
{
    public $defaultAction = 'amp';
    public $layout = '@wdmg/amp/views/layouts/amp';

    /**
     * Displays the rss-feed in xml for frontend.
     *
     * @return string
     */
    public function actionAmp($url = null) {

        $module = $this->module;
        if ($url) {

            $url = Url::to($url, true);
            /*if ($query = Yii::$app->request->getQueryString())
                $url .= '/?' .$query;*/

            if ($page = $module->findPage($url)) {
                $this->getView()->clear();
                return $this->render('index', [
                    'module' => $module,
                    'page' => $page
                ]);
            }
        }
        throw new NotFoundHttpException();
    }
}
