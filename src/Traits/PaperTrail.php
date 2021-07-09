<?php

namespace Logispot\PaperTrail\Traits;
use Illuminate\Support\Facades\Request;

/**
 * Class PaperTrail
 * @package Logispot\PaperTrail
 */
trait PaperTrail
{
    /**
     * @var array
     */
    private $originalTrail = [];

    /**
     * @var array
     */
    private $updatedTrail = [];

    /**
     * @var boolean
     */
    private $updating = false;

    static $CREATED = 'created';
    static $UPDATED = 'updated';
    static $DELETED = 'deleted';

    public static function bootPaperTrail()
    {
        static::saving(function ($model) {
            $model->onSaving();
        });

        static::saved(function ($model) {
            $model->onSaved();
        });

        static::created(function ($model) {
            $model->onCreated();
        });

        static::deleted(function ($model) {
            $model->onSaving();
            $model->onDeleted();
        });
    }

    /**
     * Load a model for paper trails
     *
     * @return mixed
     */
    public static function paperTrailModel()
    {
        $model = config('papertrail.model');
        return new $model;
    }

    /**
     * @return mixed
     */
    public function paperTrails()
    {
        $model = config('papertrail.model');
        return $this->morphMany(new $model, 'reference');
    }

    /**
     * Invoked before a model is saved. Return false to abort the operation.
     *
     * @return bool
     */
    public function onSaving()
    {
        if (!isset($this->shouldTrace) || $this->shouldTrace) {
            $this->originalTrail = $this->original;
            $this->updatedTrail = $this->attributes;
            $this->updating = $this->exists;
        }
    }

    /**
     * Create paper trail records and invoke event
     *
     * @param bool $isDeletion
     */
    private function createPaperTrails($isDeletion = false)
    {
        if ($isDeletion) {
            $description = self::$DELETED;
        } else {
            if ($this->updating) {
                $description = self::$UPDATED;
            } else {
                $description = self::$CREATED;
            }
        }

        if (isset($this->trailLimit) && $this->paperTrails()->count() >= $this->trailLimit) {
            $overLimit = true;
        } else {
            $overLimit = false;
        }

        $changedAttributes = $this->changedAttributes($isDeletion);
        $paperTrails = [];

        foreach ($changedAttributes as $key => $change) {
            $user = $this->userInformation();
            $oldValue = ($this->updating) ? ($this->originalTrail[$key] ?? null) : null;
            $newValue = $this->updatedTrail[$key];

            if ($oldValue != $newValue) {
                $paperTrails[] = [
                    'description' => $description,
                    'reference_type' => $this->getMorphClass(),
                    'reference_id' => $this->getKey(),
                    'key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'user_id' => $user['id'],
                    'user_type' => $user['class'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($paperTrails) > 0) {
            if ($overLimit) {
                $toDelete = $this->paperTrails()->orderBy('id','asc')->limit(count($paperTrails))->get();
                foreach($toDelete as $delete){
                    $delete->delete();
                }
            }

            \DB::table(self::paperTrailModel()->getTable())->insert($paperTrails);
            \Event::dispatch('paper_trail.'.$description, [
                'model' => $this,
                'paper_trails' => $paperTrails
            ]);
        }
    }

    /**
     * Called after record has been saved (Creation is excluded)
     */
    public function onSaved()
    {
        if ((!isset($this->shouldTrace) || $this->shouldTrace)
            && $this->updating
        ) {
            $this->createPaperTrails();
        }
    }

    /**
     * Called after record has been created
     *
     * @return bool|void
     */
    public function onCreated()
    {
        if (empty($this->shouldTraceOnCreation)) {
            return false;
        }

        if ((!isset($this->shouldTrace) || $this->shouldTrace)) {
            $this->createPaperTrails();
        }

        return;
    }

    /**
     * Called after record has been deleted
     *
     * @return bool|void
     */
    public function onDeleted()
    {
        if (empty($this->shouldTraceOnDeletion)) {
            return false;
        }

        if ((!isset($this->shouldTrace) || $this->shouldTrace)) {
            $this->createPaperTrails(true);
        }

        return;
    }

    /**
     * Get all of the changes that have been made, that are also supposed
     * to have their changes recorded
     *
     * @return array
     */
    private function changedAttributes($isDeletion = false)
    {
        $tracingAttributes = $this->attributesToTrace();
        $changedAttributes = [];
        $attributes = ($isDeletion) ? $this->updatedTrail : $this->getDirty();
        foreach ($attributes as $key => $value) {
            if (in_array($key, $tracingAttributes)) {
                $changedAttributes[$key] = $value;
            }
        }

        return $changedAttributes;
    }

    /**
     * Determine which field to trace
     *
     * @return array
     */
    private function attributesToTrace()
    {
        $attributes = [];
        if (isset($this->trailsToRecord) && is_array($this->trailsToRecord)) {
            if (in_array('*', $this->trailsToRecord) || !count($this->trailsToRecord)) {
                $attributes = array_merge($attributes, array_keys($this->getAttributes()));
            } else {
                $attributes = $this->trailsToRecord;
            }
        }

        if (isset($this->trailsToIgnore) && is_array($this->trailsToIgnore)) {
            $attributes = array_diff($attributes, $this->trailsToIgnore);
        }

        return $attributes;
    }

    /**
     * Get user data using Laravel Auth
     *
     * @return array
     */
    private function userInformation()
    {
        $user = \Auth::guard()->user();
        if ($user) {
            return [
                'id' => $user->id,
                'class' => get_class($user)
            ];
        } else {
            return [
                'id' => null,
                'class' => null
            ];
        }
    }

    /**
     * @param int $limit
     * @param string $order
     * @return mixed
     */
    public static function getPaperTrails($limit = 100, $order = 'desc')
    {
        $model = self::paperTrailModel();
        return $model->where('reference_type', get_called_class())
            ->orderBy('updated_at', $order)->limit($limit)->get();
    }
}