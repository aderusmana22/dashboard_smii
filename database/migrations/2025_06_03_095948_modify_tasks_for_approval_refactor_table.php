    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up()
        {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'cancel_reason')) {
                    $table->text('cancel_reason')->nullable()->after('closed_at');
                }
                if (!Schema::hasColumn('tasks', 'requester_confirmation_cancel')) {
                    $table->boolean('requester_confirmation_cancel')->default(false)->after('cancel_reason');
                }

                if (Schema::hasColumn('tasks', 'status')) {
                    $table->string('status')->default('pending_approval')->comment('Status: pending_approval, rejected, open, completed, closed, cancelled')->change();
                }


                if (Schema::hasColumn('tasks', 'approver_id')) {
                    $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('tasks');
                    foreach ($foreignKeys as $foreignKey) {
                        if (in_array('approver_id', $foreignKey->getLocalColumns())) {
                            $table->dropForeign(['approver_id']);
                            break;
                        }
                    }
                    $table->dropColumn('approver_id');
                }
                if (Schema::hasColumn('tasks', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('tasks', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
                if (Schema::hasColumn('tasks', 'approval_token')) {
                    $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('tasks');
                    if (isset($indexes['tasks_approval_token_unique'])) {
                        $table->dropIndex('tasks_approval_token_unique');
                    } elseif (isset($indexes['approval_token'])) {
                         $table->dropIndex(['approval_token']);
                    }
                    $table->dropColumn('approval_token');
                }
            });
        }

        public function down()
        {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'approver_id')) {
                    $table->foreignId('approver_id')->nullable()->constrained('users')->after('closed_at');
                }
                if (!Schema::hasColumn('tasks', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approver_id');
                }
                if (!Schema::hasColumn('tasks', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('tasks', 'approval_token')) {
                    $table->string('approval_token')->nullable()->unique()->after('rejection_reason');
                }

                if (Schema::hasColumn('tasks', 'cancel_reason')) {
                    $table->dropColumn('cancel_reason');
                }
                if (Schema::hasColumn('tasks', 'requester_confirmation_cancel')) {
                    $table->dropColumn('requester_confirmation_cancel');
                }

            });
        }
    };