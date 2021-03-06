<?php

namespace Paperwork\Db;

use Illuminate\Config\Repository;

class PaperworkDbNoteObject extends PaperworkDbObject {

	public function get($argv = array()) {
        //the version_id has to be included here or the eager load below will fail
        //also, all off the ids of the relationships have to be here also, or it will also fail
		$defaultNotesSelect = array('notes.id', 'notes.notebook_id', 'notes.created_at', 'notes.updated_at','notes.version_id');
		$defaultVersionsSelect = array('versions.id','versions.title', 'versions.content_preview', 'versions.content');
		$defaultTagsSelect = array('tags.id','tags.visibility', 'tags.title');

		$userId = $this->getArg($argv, 'userid');
		$id = $this->getArg($argv, 'id');
		$notebookId = $this->getArg($argv, 'notebookid');

		$data = \Note::with(array(
			'version' => function($query) use(&$defaultVersionsSelect) {
				$query->select($defaultVersionsSelect);
			},
			'tags' => function($query) use(&$defaultTagsSelect) {
				$query->select($defaultTagsSelect);
			}
		))->join('note_user', function($join) use(&$userId) {
				$join->on('note_user.note_id', '=', 'notes.id')
					->where('note_user.user_id', '=', $userId);
		})->select($defaultNotesSelect);

		$idCount = count($id);
		if($idCount > 0) {
			$data->where('notes.id', '=', $argv['id'][0]);
		}
		if($idCount > 1) {
			for($i = 1; $i < $idCount; $i++) {
				$data->orWhere('notes.id', '=', $argv['id'][$i]);
			}
		}
		if(isset($notebookId) && $notebookId != \PaperworkDb::DB_ALL_ID) {
			$data->where('notes.notebook_id', '=', $notebookId);
		}
		$data->whereNull('deleted_at');

		return $data->get();
	}

}

?>