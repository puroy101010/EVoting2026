<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminAccount;
use App\Models\NonMemberAccount;
use Spatie\Permission\Traits\HasRoles;



/**
 * @method bool hasRole(string $role)
 */

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password',
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected function performDeleteOnModel()
    {
        $this->deletedAt = $this->freshTimestamp(); // Set the deleted_at column
        $this->deletedBy = Auth::user()->id; // Set the value of deletedBy column using the currently authenticated user
        $this->save();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updatedBy');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deletedBy');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restoredBy');
    }

    public function adminLevel()
    {

        return $this->hasOne(AdminAccount::class, 'userId', 'id');
    }

    public function stockholder()
    {
        return $this->hasOne(Stockholder::class, 'userId', 'id');
    }

    public function stockholderAccount()
    {
        return $this->hasOne(StockholderAccount::class, 'userId', 'id');
    }

    public function nonMemberAccount()
    {
        return $this->hasOne(NonMemberAccount::class, 'userId', 'id');
    }

    public function adminAccount()
    {
        return $this->hasOne(AdminAccount::class, 'userId', 'id')->withTrashed();
    }

    public function isVoterGroup()
    {

        if (Auth::check() && (in_array($this->role, ['stockholder', 'corp-rep', 'non-member']))) {
            return true;
        }
        return false;
    }

    public function isAdminGroup()
    {
        if (Auth::check() && ($this->role === 'admin' || $this->role === 'superadmin')) {
            return true;
        }
        return false;
    }



    // used in 'StockholderController@load_option_assignees'
    public function getFullNameAttribute()
    {
        switch ($this->role) {

            case 'stockholder':
                return $this->stockholder->stockholder;

            case 'corp-rep':
                // return $this->stockholderAccount->corpRep;
                return $this->stockholderAccount->stockholder->stockholder . ($this->stockholderAccount->corpRep === null ?  '' : ' | '  . $this->stockholderAccount->corpRep);

            case 'non-member':
                return $this->nonMemberAccount->firstName . ' ' . $this->nonMemberAccount->lastName;

            case 'admin':
                return $this->adminAccount->firstName . ' ' . $this->adminAccount->lastName;

            case 'superadmin':
                return $this->adminAccount->firstName . ' ' . $this->adminAccount->lastName;

            default:
                return null;
        }
    }


    public function getAccountNoAttribute()
    {
        switch ($this->role) {

            case 'stockholder':
                return $this->stockholder->accountNo;

            case 'corp-rep':
                return $this->stockholderAccount->stockholder->accountNo;

            case 'non-member':
                return $this->nonMemberAccount->nonmemberAccountNo;


            default:
                return null;
        }
    }


    public function getAccountKeyAttribute()
    {
        switch ($this->role) {

            case 'stockholder':
                return $this->stockholder->accountNo;

            case 'corp-rep':
                return $this->stockholderAccount->accountKey;

            case 'non-member':
                return $this->nonMemberAccount->nonmemberAccountNo;


            default:
                return null;
        }
    }

    public function collectedProxy()
    {

        return $this->hasMany(ProxyBoardOfDirector::class, 'assigneeId', 'id');
    }


    public function collectedProxyAmendment()
    {

        return $this->hasMany(ProxyAmendment::class, 'assigneeId', 'id');
    }





    public function getVoteInPersonAttribute()
    {
        switch ($this->role) {

            case 'stockholder':
                return $this->stockholder->voteInPerson;

            case 'corp-rep':
                return $this->stockholderAccount->stockholder->voteInPerson;


            default:
                return null;
        }
    }




    //    proxyBoard()
}
