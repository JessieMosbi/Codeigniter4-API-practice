<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

/**
 * Model for user_basic Table.
 */
class MemberModel extends Model
{
    /**
     * @var string
     */
    protected $DBGroup = 'member';

    /**
     * @var string
     */
    protected $table = 'user_basic';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    protected $useAutoIncrement = true;

    /**
     * @var string
     */
    protected $returnType = 'object';

    /**
     * @var bool
     */
    protected $useSoftDeletes = false;

    /**
     * @var string[]
     */
    // protected $allowedFields = [];

    /**
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * @var string
     */
    protected $updatedField = 'updated_at';

    /**
     * @var string[]
     */
    // protected $beforeInsert = [''];

    /**
     * @var string[]
     */
    // protected $beforeUpdate = [''];

    /**
     * Get user by school, status
     *
     * @param array $params
     * @return object
     */
    public function findMembersBySchoolAndStatus($params)
    {
        $builder = $this->db->table('user_basic');
        $builder
            ->select('user_basic.id, user_basic.name, sex, identification, school_id, status.name AS status')
            ->join('school', 'school_id = school.id')
            ->join('status', 'status_id = status.id');
        if (isset($params['status_id']) && !empty($params['status_id'])) {
            $builder
                ->where('status.id', $params['status_id']);
        }
        if (isset($params['school_id']) && !empty($params['school_id'])) {
            $builder
                ->where('school.no', $params['school_id']);
        }
        // $sql = $builder->getCompiledSelect();
        // echo $sql;
        return $builder->get()->getResultArray();
    }
}
