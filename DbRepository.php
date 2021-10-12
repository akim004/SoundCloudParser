<?php

class DbRepository
{
	private $db;

	public function __construct($db)
    {
        $this->db = $db;
    }

	public function saveUser($userData)
	{
		try{
			$user = $this->db->where('sound_user_id', $userData['id'])->limit(1)->get('tbl_user');

			if (!$user) {
				$params = [
					'username' => $userData['username'],
					'name' => $userData['first_name'],
					'sound_user_id' => $userData['id'],
					'city' => $userData['city'],
					'followers_count' => $userData['followers_count']
				];

				$this->db->insert('tbl_user', $params);
				$user = $this->db->where('id', $this->db->insert_id())->limit(1)->get('tbl_user');
			}
		}catch(Exception $e){
			echo 'Caught exception: ', $e->getMessage();
		}

		return $user;
	}

	public function saveTracks($userId, $tracks)
	{
		try{
			if ($user = $this->db->where('id', $userId)->limit(1)->get('tbl_user')) {
				$count = 0;
				foreach ($tracks as $trackData) {
					$count++;
					$track = $this->db->where('sound_track_id', $trackData['id'])->limit(1)->get('tbl_track');
					if (!$track) {
						$params = [
							'duration' => $trackData['duration'],
							'name' => $trackData['title'],
							'playback_count' => $trackData['playback_count'],
							'comment_count' => $trackData['comment_count'],
							'sound_track_id' => $trackData['id'],
							'user_id' => $userId
						];

						$this->db->insert('tbl_track', $params);
					}
				}
			}
			return true;

		}catch(Exception $e){
			echo 'Caught exception: ', $e->getMessage();
		}
	}
}
