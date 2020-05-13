<?php
use Migrations\AbstractMigration;

class AddBiographyToAuthors extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('authors');
        $table->addColumn('biography', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }

    public function down()
    {
        $table = $this->table('authors');
        $table->removeColumn('biography');
    }
}
