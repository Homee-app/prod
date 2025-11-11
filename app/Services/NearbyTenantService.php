<?php
namespace App\Services;

use App\QueryBuilders\NearbyTenantQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class NearbyTenantService
{
    protected $user;
    protected $filters;
    protected $currentUserAnswers;
    protected $lifestyleQuestionIds;

    public function __construct($user, array $filters, array $currentUserAnswers, array $lifestyleQuestionIds)
    {
        $this->user = $user;
        $this->filters = $filters;
        $this->currentUserAnswers = $currentUserAnswers;
        $this->lifestyleQuestionIds = $lifestyleQuestionIds;
    }

    public function getNearbyTenants(int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = new NearbyTenantQuery(
            \App\Models\User::query(),
            $this->user,
            $this->filters,
            $this->currentUserAnswers
        );

        $query = $queryBuilder->build();
        $query->with('availabilityDateAnswer');

         // Prevent duplicate users if joins cause duplication
        $query->distinct('users.id');
        // Simply paginate results without additional filtering
        
        $tenants_data = $query->paginate($perPage);

        if(isset($_REQUEST['data_check'])){
            pree($tenants_data);
        }
         //pree($tenants_data);
        //pree($tenants_data, 'NearbyTenantService::getNearbyTenants');

        return $tenants_data;
    }
}
