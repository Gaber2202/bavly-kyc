<?php

namespace App\Services\Kyc;

use App\Http\Requests\Kyc\StoreKycRecordRequest;
use App\Http\Requests\Kyc\UpdateKycRecordRequest;
use App\Models\KycRecord;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class KycRecordService
{
    public function create(StoreKycRecordRequest $request): KycRecord
    {
        $data = $request->validated();
        $data['assigned_to'] = $data['assigned_to'] === '' ? null : $data['assigned_to'];
        unset($data['created_by'], $data['updated_by']);

        $actor = $request->user();

        return DB::transaction(function () use ($data, $actor) {
            $record = new KycRecord($data);
            $record->created_by = $actor->id;
            $record->save();

            ActivityLogger::log($actor, 'kyc.created', $record);

            return $record;
        });
    }

    public function update(KycRecord $record, UpdateKycRecordRequest $request): KycRecord
    {
        $data = $request->validated();
        $data['assigned_to'] = $data['assigned_to'] === '' ? null : $data['assigned_to'];
        unset($data['created_by'], $data['updated_by']);

        $actor = $request->user();

        return DB::transaction(function () use ($record, $data, $actor) {
            $record->fill($data);
            $record->updated_by = $actor->id;
            $record->save();

            ActivityLogger::log($actor, 'kyc.updated', $record);

            return $record->fresh();
        });
    }

    public function softDelete(KycRecord $record, User $actor): void
    {
        DB::transaction(function () use ($record, $actor) {
            $record->delete();

            ActivityLogger::log($actor, 'kyc.deleted', $record, [
                'client_full_name' => $record->client_full_name,
            ]);
        });
    }
}
