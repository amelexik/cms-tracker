<?php

namespace skeeks\modules\cms\tracker\controllers;

use skeeks\cms\base\Controller;

/**
 * Created by PhpStorm.
 * User: amelexik
 * Date: 19.03.19
 * Time: 17:45
 */
Class ServiceController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'track-url' => ['POST']
                ],
            ],
        ];
    }

    public function actionTrackUrl()
    {
        if ($url = \Yii::$app->request->referrer) {
            $url = ltrim(parse_url($url, PHP_URL_PATH),'/');
            \Yii::$app->tracker->trackUrl($url);
        }
    }
}