<?php

namespace Namaa\Namaa\Traits;

use Illuminate\Support\Str;

trait HasS3Objects
{
    public static function bootHasS3Objects(): void
    {
        self::created(function ($model) {
            $updateDate = [];
            foreach ($model->s3Object as $value) {
                $updateDate[$value] = $model[$value];
            }
            $model->update($updateDate);
        });

        self::updating(function ($model) {

            if ($model->table == 'companies') {
                $folder = 'public/company_' . $model->id . '/' . $model->table . '/';
            } else if (Str::contains(app()->request->getRequestUri(), '/admin-panal/')) {
                $folder = 'public/admin' . '/' . $model->table . '/';
            }else{
                $folder = 'public/company_' . app('currentTenant')->company_id . '/' . $model->table . '/';
            }
            foreach ($model->s3Object as $value) {
                if (array_key_exists($value, $model->attributes) && Str::contains($model->attributes[$value], 'temp/')) {
                    $model->attributes[$value] = str_replace(env('AWS_S3_URL'),'',$model->attributes[$value]);

                    $attFolder = $folder . $model->id . '/' . $value . '/';
                    $destPath = $attFolder . sha1(time()) . '.' . pathinfo($model->attributes[$value], PATHINFO_EXTENSION);

                    S3Helper::getInstance()->copy($model->attributes[$value], $destPath);
                    $model[$value] = $destPath;
                }else{
                    $model[$value] = str_replace(env('AWS_S3_URL'),'',$model->attributes[$value]);
                }
            }
        });
    }
}
