<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for class question.
 *
 * @package    mod_livequiz
 * @category   test
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_livequiz;

use advanced_testcase;
use mod_livequiz\models\answer;
use mod_livequiz\models\question;
use ReflectionClass;
use ReflectionException;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../test_utility.php');

/**
 * Test class for livequiz class
 */
final class question_test extends advanced_testcase {
    /**
     * @var question $question The id of the question.
     */
    private question $question;

    /**
     * Setup that runs before each test in the file
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->question = new question(
            "Question 1",
            "This is the description for the question",
            55,
            "This is the explanation for the question"
        );
    }

    /**
     * Test of get_hasmultipleanswers returns true, when there are more than 1 correct answer.
     * @covers       \mod_livequiz\models\question::get_hasmultiplecorrectanswers
     */
    public function test_question_get_hasmultipleanswers_true(): void {
        $mockanswers = [];
        for ($x = 0; $x <= 3; $x++) {
            // Create a mock object of the answer class.
            $mock = $this->getMockBuilder(answer::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['get_correct'])
                ->getMock();
            // Make the first 2 mock objects return 0 and the last 2 return 1.
            if ($x < 2) {
                $mock->expects($this->any())
                    ->method('get_correct')
                    ->willReturn(0);
            } else {
                $mock->expects($this->any())
                    ->method('get_correct')
                    ->willReturn(1);
            }
            $mockanswers[] = $mock;
        }
        $this->question->add_answers($mockanswers);
        $this->assertTrue($this->question->get_hasmultiplecorrectanswers());
    }

    /**
     * Test of get_hasmultipleanswers returns false, when there is only 1 correct answer.
     * @covers       \mod_livequiz\models\question::get_hasmultiplecorrectanswers
     */
    public function test_question_get_hasmultipleanswers_false(): void {
        $mockanswers = [];
        for ($x = 0; $x <= 3; $x++) {
            // Create a mock object of the answer class.
            $mock = $this->getMockBuilder(answer::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['get_correct'])
                ->getMock();
            // Make the first 3 mock objects return 0 and the last 1 return 1.
            if ($x < 3) {
                $mock->expects($this->any())
                    ->method('get_correct')
                    ->willReturn(0);
            } else {
                $mock->expects($this->any())
                    ->method('get_correct')
                    ->willReturn(1);
            }
            $mockanswers[] = $mock;
        }
        $this->question->add_answers($mockanswers);
        $this->assertnotTrue($this->question->get_hasmultiplecorrectanswers());
    }

    /**
     * Test of prepare_for_template for question class.
     * @covers       \mod_livequiz\models\question::prepare_for_template
     * @dataProvider dataprovider
     * prepare__for_template() should return a stdClass object, data, for use in mustache templates
     *  data should have the fields: questionid, questiontitle, questiondescription,
     *  questiontimelimit, questionexplanation, answertype, answers[]
     * The answer[] should hold the following fields: answerid, answerdescription,
     *  answerexplanation, answercorrect
     * @throws ReflectionException
     */
    public function test_question_prepare_for_template(
        int $id,
        string $title,
        string $description,
        int $timelimit,
        string $explanation,
        array $answers
    ): void {
        $originalquestion = new question($title, $description, $timelimit, $explanation);

        $classquestion = new ReflectionClass(question::class);
        $methodquestion = $classquestion->getMethod('set_id');
        $methodquestion->invokeArgs($originalquestion, (array)$id);

        $mockanswers = [];
        foreach ($answers as $answer) {
            // Create a mock object of the question class and defining which methods should be mocked.
            $mock = $this->getMockBuilder(answer::class)
                ->disableOriginalConstructor()
                ->onlyMethods([
                    'get_id',
                    'get_correct',
                    'get_description',
                    'get_explanation'])
                ->getMock();

            // Define what the mock should return when the mocked methods are called.
            $mock->expects($this->any()) // Defines that the method can be called any number of times.
                ->method('get_id') // Defines which method we are defining the mock for.
                ->willReturn($answer["id"]); // Defines what should be returned when the mocked function is called.

            $mock->expects($this->any())
                ->method('get_correct')
                ->willReturn($answer["correct"]);

            $mock->expects($this->any())
                ->method('get_description')
                ->willReturn($answer["description"]);

            $mock->expects($this->any())
                ->method('get_explanation')
                ->willReturn($answer["explanation"]);

            $mockanswers[] = $mock;
        }
        $originalquestion->add_answers($mockanswers);

        $data = new stdClass();
        $data = $originalquestion->prepare_for_template($data);

        // Verify correct question id.
        $this->assertIsInt($data->questionid);
        $this->assertEquals($originalquestion->get_id(), $data->questionid);

        // Verify correct question title.
        $this->assertIsString($data->questiontitle);
        $this->assertEquals($originalquestion->get_title(), $data->questiontitle);

        // Verify correct question description.
        $this->assertIsString($data->questiondescription);
        $this->assertEquals($originalquestion->get_description(), $data->questiondescription);

        // Verify correct question timelimit.
        $this->assertIsInt($data->questiontimelimit);
        $this->assertEquals($originalquestion->get_timelimit(), $data->questiontimelimit);

        // Verify correct question explanation.
        $this->assertIsString($data->questionexplanation);
        $this->assertEquals($originalquestion->get_explanation(), $data->questionexplanation);

        // Verify correct question amount.
        $this->assertSameSize($originalquestion->get_answers(), $data->answers);

        // Verify correct answer type.
        $this->assertIsString($data->answertype);
        if ($originalquestion->get_hasmultiplecorrectanswers()) {
            $this->assertEquals('checkbox', $data->answertype);
        } else {
            $this->assertEquals('radio', $data->answertype);
        }

        // Verify correct order of answers.
        $counter = 0;
        foreach ($originalquestion->get_answers() as $answer) {
            $this->assertIsInt($data->answers[$counter]['answerid']);
            $this->assertEquals($answer->get_id(), $data->answers[$counter]['answerid']);

            $this->assertIsString($data->answers[$counter]['answerdescription']);
            $this->assertEquals($answer->get_description(), $data->answers[$counter]['answerdescription']);

            $this->assertIsString($data->answers[$counter]['answerexplanation']);
            $this->assertEquals($answer->get_explanation(), $data->answers[$counter]['answerexplanation']);

            $this->assertIsInt($data->answers[$counter]['answercorrect']);
            $this->assertEquals($answer->get_correct(), $data->answers[$counter]['answercorrect']);

            $counter++;
        }
    }

    /**
     * Function that defines the data provider, such that a test can be run multiple times with different data
     * @return array
     */
    public static function dataprovider(): array {
        $answer1 = test_utility::createanswerarray(1, 'This is answer 1', 'This is explanation 1', 1);
        $answer2 = test_utility::createanswerarray(2, 'This is answer 2', 'This is explanation 2', 0);
        $answer3 = test_utility::createanswerarray(3, 'This is answer 3', 'This is explanation 3', 0);
        $answer4 = test_utility::createanswerarray(4, 'This is answer 4', 'This is explanation 4', 1);

        return [
            test_utility::createquestionarray(
                1,
                'question1',
                'This is the description for question 1',
                5,
                'This is the explanation for question 1',
                [$answer1, $answer2, $answer3]
            ),
            test_utility::createquestionarray(
                2,
                'question2',
                'This is the description for question 2',
                10,
                'This is the explanation for question 2',
                [$answer1, $answer2, $answer3, $answer4]
            ),
        ];
    }
}
