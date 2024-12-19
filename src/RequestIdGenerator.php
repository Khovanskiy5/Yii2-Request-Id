<?php


namespace khovanskiy\yii2requestid;

interface RequestIdGenerator
{
    public function generateRequestId(): string;
}