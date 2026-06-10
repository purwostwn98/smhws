<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackKonselingModel extends Model
{
    protected $table      = 'feedback_konseling';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'janji_id', 'user_id', 'rating', 'komentar',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    protected $validationRules = [
        'janji_id' => 'required|is_natural_no_zero',
        'user_id'  => 'required|is_natural_no_zero',
        'rating'   => 'required|is_natural|greater_than[0]|less_than[6]',
    ];

    public function byJanji(int $janjiId): ?array
    {
        return $this->where('janji_id', $janjiId)->first();
    }
}
