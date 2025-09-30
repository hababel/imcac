<?php

namespace App\Model;

use Core\Lib\Model;

class SubmissionAnswer extends Model
{
	public function saveAll(int $submissionId, array $answers): void
	{
		foreach ($answers as $questionId => $answerData) {
			$this->insert('submission_answers', [
				'submission_id' => $submissionId,
				'question_id' => $questionId,
				'answer_id' => $answerData['answer_id'],
				'points_awarded' => $answerData['points']
			]);
		}
	}
}
