<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_super_admin' => false,
            //'memberships' => EntityMembershipResource::collection($this->entityMemberships),
            //'disks' => StorageDiskResource::collection(StorageDisk::all()),
            //'data' => Mothership::make()
            //    // Only around 245 characters can be used (https://stackoverflow.com/a/18845100/9289888)
            //    ->encrypt([
            //        'id' => $this->id,
            //        'canTagMemberships' => $this->entityMemberships->where('can_tag', true)->pluck('id')->toArray(),
            //        // ToDo: scope
            //    ]),
        ];
    }
}
