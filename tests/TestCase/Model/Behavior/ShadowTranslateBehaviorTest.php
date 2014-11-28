<?php
namespace ShadowTranslate\Test\TestCase\Model\Behavior;

use Cake\I18n\I18n;
use Cake\ORM\Table as CakeTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Test\TestCase\Model\Behavior\TranslateBehaviorTest;

/**
 * Test Table class
 *
 * The sole purpose of this class is to hijack behavior loading to substitude
 * The translate behavior with the shadow translate behavior. This allows the
 * core test case to be used to verify as close as possible that the shadow
 * translate behavior is functionally equivalent to the core behavior.
 */
class Table extends CakeTable {

	public function addBehavior($name, array $options = []) {
		if ($name === 'Translate') {
			$name = 'ShadowTranslate.ShadowTranslate';
		}
		$this->_behaviors->load($name, $options);
	}

}

/**
 * ShadowTranslateBehavior test case
 */
class ShadowTranslateBehaviorTest extends TranslateBehaviorTest {

	public $fixtures = [
		'core.Articles',
		'core.Authors',
		'core.Comments',
		'plugin.ShadowTranslate.ArticlesTranslations',
		'plugin.ShadowTranslate.AuthorsTranslations',
		'plugin.ShadowTranslate.CommentsTranslations'
	];

/**
 * Seed the table registry with this test case's Table class
 *
 * @return void
 */
	public function setUp() {
		$aliases = ['Articles', 'Authors', 'Comments'];
		$options = ['className' => 'ShadowTranslate\Test\TestCase\Model\Behavior\Table'];

		foreach ($aliases as $alias) {
			TableRegistry::get($alias, $options);
		}

		parent::setUp();
	}

/**
 * Allow usage without specifying fields explicitly
 *
 * @return void
 */
	public function testAutoFieldDetection() {
		$table = TableRegistry::get('Articles');
		$table->addBehavior('Translate');

		$expected = ['title', 'body'];
		$result = $table->behaviors()->get('ShadowTranslate')->config('fields');
		$this->assertSame(
			$expected,
			$result,
			'If no fields are specified, they should be derived from the schema'
		);
	}

/**
 * Tests that after deleting a translated entity, all translations are also removed
 *
 * @return void
 */
	public function testDelete() {
		$table = TableRegistry::get('Articles');
		$table->addBehavior('Translate', ['fields' => ['title', 'body']]);
		$article = $table->find()->first();
		$this->assertTrue($table->delete($article));

		$translations = TableRegistry::get('ArticlesTranslations')->find()
			->where(['id' => $article->id])
			->count();
		$this->assertEquals(0, $translations);
	}

}
