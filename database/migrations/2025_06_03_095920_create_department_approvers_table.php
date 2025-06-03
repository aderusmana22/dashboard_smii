    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up()
        {
            Schema::create('department_approvers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
                $table->string('user_nik')->comment('NIK user yang ditunjuk sebagai approver untuk departemen ini');
                $table->string('status')->default('active')->comment('active, inactive');
                $table->timestamps();

                $table->unique(['department_id', 'user_nik']);
                $table->foreign('user_nik')->references('nik')->on('users')->onDelete('cascade');
            });
        }

        public function down()
        {
            Schema::dropIfExists('department_approvers');
        }
    };