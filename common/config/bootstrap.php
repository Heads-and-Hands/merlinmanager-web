<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

Yii::setAlias('@rscPath', dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR
    . join(DIRECTORY_SEPARATOR,['backend','web','rsc']));
Yii::setAlias('@filePath', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR
    . join(DIRECTORY_SEPARATOR, ['backend', 'web', 'tmp']));
Yii::setAlias('@webPath', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR
    . join(DIRECTORY_SEPARATOR, ['backend', 'web']));