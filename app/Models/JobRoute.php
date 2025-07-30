<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class JobRoute extends Model {
    use HasFactory;
    protected $table = 'job_routes';
    const UPDATED_AT = null;
    protected $fillable = ['job_id', 'from_department_id', 'to_department_id', 'note', 'created_by', 'created_at'];
    public function job() { return $this->belongsTo(JobMarsho::class, 'job_id'); }
    public function fromDepartment() { return $this->belongsTo(Department::class, 'from_department_id'); }
    public function toDepartment() { return $this->belongsTo(Department::class, 'to_department_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}