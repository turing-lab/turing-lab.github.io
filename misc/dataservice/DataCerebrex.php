<?php

define("DATABASE_SERVER", "localhost");
define("DATABASE_USERNAME", "id270868_cerebrex");
define("DATABASE_PASSWORD", "cerebrexgame");
define("DATABASE_NAME", "id270868_cerebrex_publico"); 

/*
define("DATABASE_SERVER", "localhost");
define("DATABASE_USERNAME", "root");
define("DATABASE_PASSWORD", "root");
define("DATABASE_NAME", "dbCerebrex");
*/
class DataCerebrex {
	/*
	* TODO: 
	*	
	*/

	/**
	 * @param string $user
	 * @return boolean 
	 * checks if a user is already stored
	 */
	public function check_existing_user ($user) {		
		$user = filter_var($user, FILTER_SANITIZE_STRING);		
		$return_value = false;
				
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT 1 FROM users where username='" . $user . "'";
			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {
					$return_value = true;
				}
				mysql_close($mysql);
			}
		}	
		return $return_value;
	}


	/**
	 * @param string $email
	 * @return boolean 
	 * checks if an email is already stored
	 */
	public function check_existing_email ($email) {
		$email = filter_var($email, FILTER_SANITIZE_STRING);		
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT 1 FROM accounts where account_type='email' AND account_handler='" . $email . "'";
			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {					
					$return_value = true;
				}
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}
		
	/**
	 * @param string $nombre
	 * @param string $password (en md5)
	 * @return int $user_id
	 */
	public function signin($user,$pass){
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pass = filter_var($pass, FILTER_SANITIZE_STRING);
				
		$return_value = -1;				
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT id FROM users where username='" . $user . "' AND password='" . $pass . "'";
			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);				
				if ($row) {
					$return_value = $row->id;
				}
				mysql_close($mysql);
			}
		}		
		return $return_value;
	}
	
	public function get_seccion ($user_id) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT account_handler FROM `accounts` WHERE user_id=".$user_id." AND (account_type='seccion' OR account_type='grado')";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					array_push($return_value,$aux);
				}				
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}
			
	/**
	 * @param string $user 	  			ykro
	 * @param string $pass    			string en md5
	 * @param string $email	  			ykro@elementalgeeks.com
	 * @param string optional $gender	M
	 * @param string optional $birthday	06071985
	 * @return int		  				user_id
	 */	
	public function signup($user, $pass, $email, $gender="", $birthday="") {
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pass = filter_var($pass, FILTER_SANITIZE_STRING);
		$email = filter_var($email, FILTER_SANITIZE_STRING);				
		$gender = filter_var($gender, FILTER_SANITIZE_STRING);				
		$birthday = filter_var($birthday, FILTER_SANITIZE_STRING);

		$return_value = -1;		
		$gender = ($gender == "")? "null" : "'" . $gender . "'";		
		$birthday = ($birthday == "")? "null" : "'" . $birthday . "'";

		/* verificamos que no exista */
		$user_exists = $this->check_existing_user($user);
		if (!$user_exists) {
			$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
			if ($mysql) {
				mysql_select_db(DATABASE_NAME);
				$query = "INSERT INTO users(username, password, gender, birthday, date_created) VALUES ('" . $user . "','" . $pass . "'," . $gender . "," . $birthday . ",now())";
				$result = mysql_query($query);
				if ($result) {
					mysql_close($mysql);
					$user_id = $this->signin($user, $pass);
					//se crean los trofeos para este usuario
					$this->create_trophys($user_id);
					$return_value = $user_id;
					if ($user_id != -1) {
						$acc = $this->add_account($user_id, "email", $email);
					}
				}
			}
		}
		return $return_value;
	}


	/**
	*@param int $user_id 7
	*/
	public function create_trophys($user_id){
		//se crean las tuplas de los trofeos para el nuvevo usuario en la tabla de trophies_earned
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT * FROM  trophies";
			$result = mysql_query($query) or die(mysql_error()); ;
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$trophy_id = $row[0];
					$query = "INSERT INTO trophies_earned(user_id, trophy_id, date_updated) VALUES ('" . $user_id . "','" . $trophy_id . "',now())";
					mysql_query($query);
				}
			}
			mysql_close($mysql);
		}
	}



	/**
	*@param int $user_id 71
	*@param int $game_id 2 (diferencias)
	*@param int $score 20 (diferencias)
	*@return void
	*/
	public function update_trophy_earned($user_id,$game_id,$score){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if($mysql){
			mysql_select_db(DATABASE_NAME);
			//se obtiene el id del trophy
			$query = "SELECT id FROM  `trophies` WHERE game_id=$game_id";
			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {
					$trophy_id = $row->id;
					//se actualizan los valore para el trophy necesario
					$query = "UPDATE `trophies_earned` SET current_score = current_score + $score where user_id=$user_id and trophy_id=$trophy_id";
					mysql_query($query);
				}				
				mysql_close($mysql);
			}
		}
	}


	/**
	 * @param int $user_id 3
	 * @param int $game_id 4
	 * @param int $score   40
	 * @return int $stat_id if success -1 if failure
	 */	
	public function set_score ($user_id, $game_id, $score) {		
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
		$score = filter_var($score, FILTER_SANITIZE_NUMBER_INT);

		$return_value = -1;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);

			$query = "INSERT INTO stats(user_id, game_id, score, group_dt, daily_test, date_created) values (" . $user_id . "," . $game_id . "," . $score . ",null,0,now())";
			$result = mysql_query($query);
			if ($result) {
				$query = "SELECT id FROM `stats` WHERE user_id = $user_id and game_id = $game_id order by id desc";
				$result = mysql_query($query);
				$row = mysql_fetch_object($result);				
				if ($row) {					
					$return_value = $row->id;
				}				
				mysql_close($mysql);
			}
		}	
		$this->update_trophy_earned($user_id,$game_id,$score);
		return $return_value;		
	}
	
	/**
	 * @param int $user_id  3
	 * @param int $game_id  4
	 * @param int $score    40
	 * @param string $group "ddmmyy-hhmm"
	 * @return boolean		true if success
	 */	
	public function set_daily_test_score ($user_id, $game_id, $score, $group) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
		$score = filter_var($score, FILTER_SANITIZE_NUMBER_INT);				
		$group = filter_var($group, FILTER_SANITIZE_STRING);		

		$return_value = false;		
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);

			$query = "INSERT INTO stats(user_id, game_id, score, group_dt, daily_test, date_created) values (" . $user_id . "," . $game_id . "," . $score . ",'" . $group . "',1,now())";
			$result = mysql_query($query);
			if ($result) {
				$query = "SELECT id FROM `stats` WHERE user_id = $user_id and game_id = $game_id order by id desc";
				$result = mysql_query($query);
				$row = mysql_fetch_object($result);				
				if ($row) {					
					$return_value = $row->id;
				}				
				mysql_close($mysql);
			}
		}						
		return $return_value;		
	}
	/**
	 * @param int $user_id 3
	 * @param int $game_id 4
	 * @param string $data_type 'precision'
	 * @param float $data_value
	 * @return boolean	   true if success
	 */	
	public function set_metric($user_id, $game_id, $stats_id, $data_type,$data_value) {		
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
		$data_type = filter_var($data_type, FILTER_SANITIZE_STRING);
		$data_value = filter_var($data_value, FILTER_SANITIZE_NUMBER_INT);

		$return_value = false;		
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO metrics(user_id, game_id, stats_id,data_type, data_value,date_created) 
						values ($user_id ,$game_id , $stats_id ,'$data_type ',$data_value,now())";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;				
				mysql_close($mysql);
			}
		}
		return $return_value;
	}

	/**
	 * @param int optional $user_id  		-1
	 * @param int optional $game_id  		-1	
	 * @param int optional $limit  			20
	 * @param int optional $daily_test	false
	 * @return array [username, game_id, group, score] 
	 */	
	public function get_score ($user_id=-1, $game_id=-1, $game_id2=0, $game_id3=0, $modo=0,$limit=5, $daily_test=0) {
		$return_value = array();
		$limit = ($limit >= 100)? 100 : $limit;
		
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);

			if ($user_id == -1) { /* la respuesta ser un arreglo del top*/
				if ($game_id == -1) { /* no se de que juego es */
					if ($daily_test == 0) { /* no es un daily test */
						$query = "SELECT user_id, score, game_id FROM stats ORDER BY score DESC LIMIT 0,$limit";
					} else { /* es un daily test */
						$query = "SELECT user_id, score, game_id, group_dt, s2.prom as promedio 
								  FROM stats s1,(
									SELECT avg(score) as prom, group_dt as g 
									FROM stats 
									WHERE daily_test=1 
									GROUP BY group_dt) as s2
								  WHERE daily_test=1 AND s1.group_dt = s2.g 
								  ORDER BY s2.prom DESC LIMIT 0," . $limit*4;
					}
				} else { /* s’ se de que juego es */
					if ($daily_test == 0) { /* no es un daily test */
						if($modo==0){$query = "SELECT user_id, score FROM stats where game_id=$game_id ORDER BY score DESC LIMIT 0,$limit";}
						else{$query = "SELECT MAX(score),game_id
                                       FROM stats
                                       WHERE user_id=$user_id AND (game_id=$game_id OR game_id=$game_id2 OR game_id=$game_id3)
                                       GROUP BY game_id
                                       ORDER BY score DESC
                                       LIMIT 1";}
					} else { /* es un daily test */
						$query = "SELECT user_id, score, game_id, s2.g as group_dt, s2.prom as promedio 
								  FROM stats s1,(
									SELECT avg(score) as prom, group_dt as g 
									FROM stats 
									WHERE group_dt IN
											(SELECT DISTINCT group_dt
											FROM stats
											WHERE game_id=$game_id
											AND daily_test=1)
									GROUP BY group_dt											
									) as s2
								  WHERE daily_test=1 AND s1.group_dt = s2.g 
								  ORDER BY s2.prom DESC LIMIT 0," . $limit*4;
					}
				}			
			} else { /* la respuesta son datos de un usuario */
				$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
				$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
				$daily_test = filter_var($daily_test, FILTER_SANITIZE_NUMBER_INT);
							
				if ($game_id == -1) { /* no se de que juego es */
					if ($daily_test == 0) { /* no es un daily test */
						$query = "SELECT score, game_id FROM stats where user_id=$user_id ORDER BY score DESC LIMIT 0,$limit";
					} else { /* es un daily test */
						$query = "SELECT user_id, score, game_id, group_dt, s2.prom as promedio 
								  FROM stats s1,(
									SELECT avg(score) as prom, group_dt as g 
									FROM stats 
									WHERE daily_test=1 AND user_id=$user_id									
									GROUP BY group_dt) as s2
								  WHERE daily_test=1 AND s1.group_dt = s2.g 
								  ORDER BY s2.prom DESC LIMIT 0," . $limit*4;
					}
				} else { /* s’ se de que juego es */
					if ($daily_test == 0) { /* no es un daily test */
						if($modo==0){$query = "SELECT user_id, score FROM stats where game_id=$game_id ORDER BY score DESC LIMIT 0,$limit";}
						else{$query = "SELECT score, game_id
                                       FROM stats
                                       WHERE user_id=$user_id AND (game_id=$game_id OR game_id=$game_id2 OR game_id=$game_id3)
                                       ORDER BY score DESC
                                       LIMIT 1";}
					} else { /* es un daily test */
						$query = "SELECT user_id, score, game_id, s2.g as group_dt, s2.prom as promedio 
								  FROM stats s1,(
									SELECT avg(score) as prom, group_dt as g 
									FROM stats 
									WHERE group_dt IN
											(SELECT DISTINCT group_dt
											FROM stats
											WHERE game_id=$game_id AND user_id=$user_id
											AND daily_test=1)
									GROUP BY group_dt											
									) as s2
								  WHERE daily_test=1 AND s1.group_dt = s2.g 
								  ORDER BY s2.prom DESC LIMIT 0," . $limit*4;						
					}					
				}
			}

			$result = mysql_query($query);
			if ($result) {
			 if($modo==0){
				while ($row = mysql_fetch_object($result)){
					if ($user_id == -1) {
						array_push($return_value, $row->user_id);
					} else {
						array_push($return_value, $user_id);
					}
					if (($game_id == -1) || ($game_id != -1 && $daily_test == 1)) {
						array_push($return_value, $row->game_id);
					} else {
						array_push($return_value, $game_id);
					}
					if ($daily_test == 1) {
						array_push($return_value, $row->group_dt);						
					} else {
						array_push($return_value, "null");
					}
					array_push($return_value, $row->score);
				}
			   }
		   else{
		        while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					array_push($return_value,$aux,$aux1);
				}
		       }
				mysql_close($mysql);											
			 }							
		}
		return $return_value;
	}
	public function get_user_score($id,$game_id,$time_limit="all"){
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			
			if(strcasecmp("week",$time_limit)==0)
				$date_limit = " and `date_updated` BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()";
			else if(strcasecmp("month",$time_limit)==0)
				$date_limit = "and MONTH(NOW())=MONTH(date_updated)";
			else
				$date_limit = "";
			if(is_null($game_id)){
				$query = "SELECT date_updated, sum(score) as dt_score FROM stats WHERE user_id = $id and daily_test = 1 $date_limit group by group_dt order by group_dt asc";
			}else if (sizeof($game_id)>0){
				$query = "SELECT date_updated, score as dt_score FROM stats WHERE user_id = $id and (";
				for ($i=0; $i<sizeof($game_id); $i++) {
					$aux = $game_id[$i];
					$query = $query . "game_id = $aux ";
					if(sizeof($game_id)-1>$i) // si este elemento no es el ultimo, entonces agrega el or, y si si es, cierra el parentesis
						$query = $query . "||";
					else{
						$query = $query. ") $date_limit order by date_updated asc";
					}
				}
			}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					array_push($return_value, $aux,$aux1);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	/*
	@return array highest Scores
	SELECT X.user_id,X.username, max(sum_score) as max_score
FROM (SELECT user_id,username,sum(score) as sum_score 
     FROM `stats` as S, users as U
     WHERE user_id=U.id and daily_test = 1
     GROUP BY group_dt) as X
GROUP BY user_id
ORDER BY max_score desc
	*/
	public function get_high_score($game_id,$seccion,$grado,$scope,$from = 0){
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			if(is_null($id)){
			   if($scope==0){
			     $query = "	SELECT X.user_id,X.username, max(sum_score) as max_score
						    FROM (SELECT S.user_id,username,sum(score) as sum_score 
						         FROM `stats` as S, users as U, accounts as A
						         WHERE S.user_id=U.id AND daily_test = 1 AND A.user_id=S.user_id AND A.account_handler='".$grado."'
						         GROUP BY group_dt,S.user_id) as X
						    GROUP BY user_id
						    ORDER BY max_score desc
						    LIMIT $from,10";
			   }
			   else if($scope==1){
			     $query = "	SELECT X.user_id,X.username, max(sum_score) as max_score
		                    FROM(SELECT S.user_id,username,sum(score) as sum_score 
		                         FROM `stats` as S, users as U, accounts as A
		                         WHERE S.user_id=U.id AND daily_test = 1 AND A.user_id=S.user_id AND A.account_handler IN ('".$seccion."','".$grado."')
	                             GROUP BY group_dt,S.user_id) as X
	 	                    GROUP BY user_id
		                    ORDER BY max_score desc
		                    LIMIT $from,10";
			   }
			   else if($scope==2){
			     $query = "	SELECT X.user_id,X.username, max(sum_score) as max_score
							FROM (SELECT user_id,username,sum(score) as sum_score 
								 FROM `stats` as S, users as U
								 WHERE user_id=U.id and daily_test = 1
								 GROUP BY group_dt,user_id) as X
							GROUP BY user_id
							ORDER BY max_score desc
							LIMIT $from,10";
			   }
			}else if (sizeof($game_id)>0){
				 $query = "SELECT date_updated, score as dt_score FROM stats WHERE user_id = $id and (";
				// for ($i=0; $i<sizeof($game_id); $i++) {
					// $aux = $game_id[$i];
					// $query = $query . "game_id = $aux ";
					// if(sizeof($game_id)-1>$i) // si este elemento no es el ultimo, entonces agrega el or, y si si es, cierra el parentesis
						// $query = $query . "||";
					// else{
						// $query = $query. ") $date_limit order by date_updated asc";
					// }
				// }
			}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					array_push($return_value, $aux,$aux1,$aux2,"picUser2.png");
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_relics($user_id){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT r.id, r.name, re.quantity
                      FROM `relics_earned` AS re, `relics` AS r, `users` AS u
                      WHERE re.user_id = u.id AND re.relic_id = r.id AND u.id = $user_id
                      ORDER BY r.id";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					array_push($return_value,$aux,$aux1,$aux2);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_all_tienda_relics($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			if($idioma==1){
			$query = "SELECT DISTINCT r.id, r.name, r.rarity, r.price, r.description, re.quantity, gp.money
                      FROM `relics_earned` AS re, `relics` AS r, `general_profile` AS gp, `users` AS u
                      WHERE re.user_id = u.id AND gp.user_id = u.id AND re.relic_id = r.id AND u.id = $user_id
                      ORDER BY r.id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT r.id, r.name_english, r.rarity, r.price, r.description_english, re.quantity, gp.money
                      FROM `relics_earned` AS re, `relics` AS r, `general_profile` AS gp, `users` AS u
                      WHERE re.user_id = u.id AND gp.user_id = u.id AND re.relic_id = r.id AND u.id = $user_id
                      ORDER BY r.id";
				}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					$aux4 = $row[4];
					$aux5 = $row[5];
					$aux6 = $row[6];
					array_push($return_value,$aux,$aux1,$aux2,$aux3,$aux4,$aux5,$aux6);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_active_skills($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			
			if($idioma==1){
			$query = "SELECT DISTINCT s.id, s.name
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND se.active = 1 AND u.id = $user_id
                      ORDER BY s.id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT s.id, s.name_english
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND se.active = 1 AND u.id = $user_id
                      ORDER BY s.id";
				}

			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					array_push($return_value,$aux,$aux1);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_all_skills($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			
			if($idioma==1){
			$query = "SELECT DISTINCT s.id, s.name, s.description, CAST(se.active AS UNSIGNED)as active
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND u.id = $user_id
                      ORDER BY s.id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT s.id, s.name_english, s.description_english, CAST(se.active AS UNSIGNED)as active
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND u.id = $user_id
                      ORDER BY s.id";
				}
					  
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					array_push($return_value,$aux,$aux1,$aux2,$aux3);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_rewards($user_id,$rare1,$rare2,$rare3,$rare4,$rare5,$rare6,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			if($idioma==1){
			$query = "SELECT DISTINCT r.id, r.rarity, r.name, r.description, gp.level, gp.xp_next, gp.xp_current, gp.money, gp.total_xp
                      FROM `general_profile` AS gp, `users` AS u, `relics` AS r
                      WHERE gp.user_id = u.id AND u.id = $user_id AND (r.rarity=$rare1 OR
                                                                       r.rarity=$rare2 OR
                                                                       r.rarity=$rare3 OR
                                                                       r.rarity=$rare4 OR
                                                                       r.rarity=$rare5 OR
                                                                       r.rarity=$rare6)";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT r.id, r.rarity, r.name_english, r.description_english, gp.level, gp.xp_next, gp.xp_current, gp.money, gp.total_xp
                      FROM `general_profile` AS gp, `users` AS u, `relics` AS r
                      WHERE gp.user_id = u.id AND u.id = $user_id AND (r.rarity=$rare1 OR
                                                                       r.rarity=$rare2 OR
                                                                       r.rarity=$rare3 OR
                                                                       r.rarity=$rare4 OR
                                                                       r.rarity=$rare5 OR
                                                                       r.rarity=$rare6)";
				}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					$aux4 = $row[4];
					$aux5 = $row[5];
					$aux6 = $row[6];
					$aux7 = $row[7];
					$aux8 = $row[8];
					array_push($return_value,$aux,$aux1,$aux2,$aux3,$aux4,$aux5,$aux6,$aux7,$aux8);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_rewards2($user_id){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT DISTINCT gp.level, gp.xp_next, gp.xp_current, gp.money, gp.total_xp
                      FROM `general_profile` AS gp, `users` AS u
                      WHERE gp.user_id = u.id AND u.id = $user_id";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					$aux4 = $row[4];
					array_push($return_value,$aux,$aux1,$aux2,$aux3,$aux4);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_all_tienda_skills_needed($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			
			if($idioma==1){
			$query = "SELECT DISTINCT s.id, s.name, s.description, s.price
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE                                           s.id NOT IN (SELECT DISTINCT s2.id
                                                                                   FROM `skills_earned` AS se2, `skills` AS s2, `users` AS u2
                                                                                   WHERE se2.user_id = u2.id AND u2.id = $user_id AND s2.id = se2.skill_id
                                                                                   )
                      ORDER BY s.id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT s.id, s.name_english, s.description_english, s.price
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE                                           s.id NOT IN (SELECT DISTINCT s2.id
                                                                                   FROM `skills_earned` AS se2, `skills` AS s2, `users` AS u2
                                                                                   WHERE se2.user_id = u2.id AND u2.id = $user_id AND s2.id = se2.skill_id
                                                                                   )
                      ORDER BY s.id";
				}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					array_push($return_value,$aux,$aux1,$aux2,$aux3);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_all_tienda_skills_needed2($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			
			if($idioma==1){
			$query = "SELECT DISTINCT sr.skill_id, sr.relic_id, r.name, sr.quantity
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u, `skill_relic` AS sr, `relics` AS r
                      WHERE                                           s.id NOT IN (SELECT DISTINCT s2.id
                                                                                   FROM `skills_earned` AS se2, `skills` AS s2, `users` AS u2
                                                                                   WHERE se2.user_id = u2.id AND u2.id = $user_id AND s2.id = se2.skill_id
                                                                                   ) AND s.id = sr.skill_id AND r.id = sr.relic_id
                      ORDER BY sr.skill_id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT sr.skill_id, sr.relic_id, r.name_english, sr.quantity
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u, `skill_relic` AS sr, `relics` AS r
                      WHERE                                           s.id NOT IN (SELECT DISTINCT s2.id
                                                                                   FROM `skills_earned` AS se2, `skills` AS s2, `users` AS u2
                                                                                   WHERE se2.user_id = u2.id AND u2.id = $user_id AND s2.id = se2.skill_id
                                                                                   ) AND s.id = sr.skill_id AND r.id = sr.relic_id
                      ORDER BY sr.skill_id";
				}
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					array_push($return_value,$aux,$aux1,$aux2,$aux3);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_all_tienda_skills_got($user_id){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT DISTINCT r.id, re.quantity, gp.money
                      FROM `relics_earned` AS re, `relics` AS r, `general_profile` AS gp
                      WHERE re.user_id = $user_id AND re.relic_id = r.id AND gp.user_id = $user_id
                      ORDER BY r.id";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					array_push($return_value,$aux,$aux1,$aux2);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_active_skills_juegos($user_id,$idioma){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			if($idioma==1){
			$query = "SELECT DISTINCT s.id, s.name
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND se.active = 1 AND u.id = $user_id
                      ORDER BY s.id";
				}
			if($idioma==0){
			$query = "SELECT DISTINCT s.id, s.name_english
                      FROM `skills_earned` AS se, `skills` AS s, `users` AS u
                      WHERE se.user_id = u.id AND se.skill_id = s.id AND se.active = 1 AND u.id = $user_id
                      ORDER BY s.id";
				}
			
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					array_push($return_value,$aux,$aux1);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function get_general_profile($user_id){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT DISTINCT gp.money, gp.level, gp.total_xp, gp.xp_next, gp.xp_current, u.username
                      FROM `general_profile` AS gp, `users` AS u
                      WHERE gp.user_id = u.id AND u.id = $user_id";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					$aux2 = $row[2];
					$aux3 = $row[3];
					$aux4 = $row[4];
					$aux5 = $row[5];
					array_push($return_value,$aux,$aux1,$aux2,$aux3,$aux4,$aux5);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function update_active_skills($user_id,$a1,$a2,$a3,$a4,$a5,$a6,$a7,$a8,$a9,$a10,$a11,$a12,$a13,$a14,$a15,$a16,$a17,$a18,$a19,$a20){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `skills_earned`
                        SET `active` = CASE `skill_id`
                          WHEN 1 THEN b'$a1'
                          WHEN 2 THEN b'$a2'
                          WHEN 3 THEN b'$a3'
						  WHEN 4 THEN b'$a4'
                          WHEN 5 THEN b'$a5'
                          WHEN 6 THEN b'$a6'
						  WHEN 7 THEN b'$a7'
                          WHEN 8 THEN b'$a8'
                          WHEN 9 THEN b'$a9'
						  WHEN 10 THEN b'$a10'
						  WHEN 11 THEN b'$a11'
                          WHEN 12 THEN b'$a12'
                          WHEN 13 THEN b'$a13'
						  WHEN 14 THEN b'$a14'
                          WHEN 15 THEN b'$a15'
                          WHEN 16 THEN b'$a16'
						  WHEN 17 THEN b'$a17'
                          WHEN 18 THEN b'$a18'
                          WHEN 19 THEN b'$a19'
						  WHEN 20 THEN b'$a20'
                          END
                      WHERE `skill_id` IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20) AND `user_id` = $user_id";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function update_tienda_skills1($user_id,$a1,$a2,$a3,$a4,$a5,$a6,$a7,$a8,$a9,$a10,$a11,$a12,$a13,$a14,$a15,$a16,$a17,$a18,$a19,$a20,$a21,$a22,$a23,$a24,$a25,$a26,$a27,$a28){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `relics_earned`
                        SET `quantity` = CASE `relic_id`
                          WHEN 1 THEN $a1
                          WHEN 2 THEN $a2
                          WHEN 3 THEN $a3
						  WHEN 4 THEN $a4
                          WHEN 5 THEN $a5
                          WHEN 6 THEN $a6
						  WHEN 7 THEN $a7
                          WHEN 8 THEN $a8
                          WHEN 9 THEN $a9
						  WHEN 10 THEN $a10
						  WHEN 11 THEN $a11
                          WHEN 12 THEN $a12
                          WHEN 13 THEN $a13
						  WHEN 14 THEN $a14
                          WHEN 15 THEN $a15
                          WHEN 16 THEN $a16
						  WHEN 17 THEN $a17
                          WHEN 18 THEN $a18
                          WHEN 19 THEN $a19
						  WHEN 20 THEN $a20
						  WHEN 21 THEN $a21
                          WHEN 22 THEN $a22
                          WHEN 23 THEN $a23
						  WHEN 24 THEN $a24
                          WHEN 25 THEN $a25
                          WHEN 26 THEN $a26
						  WHEN 27 THEN $a27
                          WHEN 28 THEN $a28
                          END
                      WHERE `relic_id` IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28) AND `user_id` = $user_id";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function update_tienda_skills2($user_id,$skill_id){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO skills_earned(user_id,skill_id,active,date_updated) VALUES ('$user_id','$skill_id',b'0',now())";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function update_rewards($user_id,$money,$xp_current,$xp_next,$total_xp,$lvl){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `general_profile`
                      SET money=$money, level=$lvl, xp_next=$xp_next, xp_current=$xp_current, total_xp=$total_xp
                      WHERE user_id=$user_id;";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function update_rewards_relic($user_id,$id1,$q1,$id2,$q2,$id3,$q3,$id4,$q4,$id5,$q5,$id6,$q6){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `relics_earned`
                        SET `quantity` = CASE `relic_id`
                          WHEN $id1 THEN $q1
                          WHEN $id2 THEN $q2
                          WHEN $id3 THEN $q3
						  WHEN $id4 THEN $q4
                          WHEN $id5 THEN $q5
                          WHEN $id6 THEN $q6
                          END
                      WHERE `relic_id` IN ($id1,$id2,$id3,$id4,$id5,$id6) AND `user_id` = $user_id";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function get_a_relic_quantity($user_id,$id1,$id2,$id3,$id4,$id5,$id6){
	$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
	$return_value = array();
	$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT relic_id,quantity
                      FROM `relics_earned`
                      WHERE user_id=$user_id AND (relic_id=$id1 OR
                                                  relic_id=$id2 OR
                                                  relic_id=$id3 OR
                                                  relic_id=$id4 OR
                                                  relic_id=$id5 OR
												  relic_id=$id6)";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_row($result)){
					$aux = $row[0];
					$aux1 = $row[1];
					array_push($return_value,$aux,$aux1);
				}
			}
			mysql_close($mysql);
		}
		return $return_value;
	}
	
	public function update_tienda_skills3($user_id,$money){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `general_profile`
                      SET money=$money
                      WHERE user_id=$user_id;";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function create_account_profile($user_id){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO general_profile(user_id,money,level,total_xp,xp_next,xp_current,active_skills,date_updated)
                      VALUES ('$user_id','0','1','0','1193','0','0',now())";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	
	public function create_account_relics($user_id){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO `relics_earned` (user_id,relic_id,quantity,date_updated)
                      VALUES ('$user_id','1','0',now()),
                             ('$user_id','2','0',now()),
                             ('$user_id','3','0',now()),
							 ('$user_id','4','0',now()),
							 ('$user_id','5','0',now()),
							 ('$user_id','6','0',now()),
                             ('$user_id','7','0',now()),
                             ('$user_id','8','0',now()),
							 ('$user_id','9','0',now()),
							 ('$user_id','10','0',now()),
							 ('$user_id','11','0',now()),
                             ('$user_id','12','0',now()),
                             ('$user_id','13','0',now()),
							 ('$user_id','14','0',now()),
							 ('$user_id','15','0',now()),
							 ('$user_id','16','0',now()),
                             ('$user_id','17','0',now()),
                             ('$user_id','18','0',now()),
							 ('$user_id','19','0',now()),
							 ('$user_id','20','0',now()),
							 ('$user_id','21','0',now()),
                             ('$user_id','22','0',now()),
                             ('$user_id','23','0',now()),
							 ('$user_id','24','0',now()),
							 ('$user_id','25','0',now()),
							 ('$user_id','26','0',now()),
                             ('$user_id','27','0',now()),
                             ('$user_id','28','0',now());";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				
		return $return_value;
	}
	

	/**
	 * @return array of ids and names [1,'blah',2,'meh']
	 */	
	public function get_available_trophies() {		
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT id, trophy_name FROM trophies";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_object($result)){
					array_push($return_value, $row->id);
					array_push($return_value, $row->trophy_name);
				}
				
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}
	
	/**
	 * @param int $user_id  	3
	 * @param int $trophy_id  	4
	 * @return boolean			true if success
	 */	
	public function set_achievement($user_id, $trophy_id) {	
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$trophy_id = filter_var($trophy_id, FILTER_SANITIZE_NUMBER_INT);
		
		$return_value = false;		
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO achievements(user_id, trophy_id, date_created) values (" . $user_id . "," . $trophy_id . ",now())";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;				
				mysql_close($mysql);
			}
		}							
		return $return_value;			
	}
		
	/**
	 * @param int $user_id  	3	
	 * @return array of names ['blah','meh']
	 */	
	public function get_achievements($user_id) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
						
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT t.trophy_name FROM achievements a, trophies t WHERE a.trophy_id = t.id AND a.user_id=" . $user_id;
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_object($result)){
					array_push($return_value, $row->trophy_name);
				}
				mysql_close($mysql);
			}
		}				
		return $return_value;
	}

	/**
	* @param array of strings $friends
	* @param int $game_id
	* @return array of ids/score [1,200,2,500] 
	*/
	public function get_facebook_friends_with_scores($friends,$game_id=-1) {
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = array();
		
		foreach ($friends as $current_friend) {
			$current_friend = filter_var($current_friend, FILTER_SANITIZE_STRING);
			$user_id = $this->check_account("facebook",$current_friend);

			if ($user_id != -1) {
				$score_from_db = $this->get_score ($user_id, $game_id, 1);
				if (count($score_from_db) == 4) {
					array_push($return_value, $current_friend);
					array_push($return_value, $score_from_db[3]);					
				}
			}
		}
				
		return $return_value;
	}

	/**
	 * @param string $type		"facebook"
	 * @param string $handler	"714395194"
	 * @return int 				user_id if success
	 */
	public function check_account ($type, $handler) {
		$type = filter_var($type, FILTER_SANITIZE_STRING);		
		$handler = filter_var($handler, FILTER_SANITIZE_STRING);
		
		$return_value = -1;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT user_id FROM accounts where account_type='" . $type . "' AND account_handler='" . $handler . "'";

			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {					
					$return_value = $row->user_id;
				}
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}
	
	/**
	 * @param string $type		"facebook"
	 * @param string $handler	"714395194"
	 * @return boolean 			boolean
	 */
	public function check_account_type_for_user ($type, $user_id) {
		$type = filter_var($type, FILTER_SANITIZE_STRING);		
		$game_id = filter_var($game_id, FILTER_SANITIZE_NUMBER_INT);

		$return_value = false;
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT user_id FROM accounts where account_type='$type' AND user_id=$user_id";
			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {
					$return_value = true;
				}
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}	
		
	/**
	 * @param int $user_id 123
	 * @param string $handler juan@gmail.com
	 * @param string $type	  email
	 * @return boolean 		  true if success
	 */		
	public function add_account($user_id, $type, $handler) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$handler = filter_var($handler, FILTER_SANITIZE_STRING);
		$type = filter_var($type, FILTER_SANITIZE_STRING);

		$return_value = false;

		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "INSERT INTO accounts(user_id, account_handler, account_type, date_created) values (" . $user_id . ",'" . $handler . "','" . $type ."',now())";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				

		return $return_value;
	}
	public function update_password($user_id,$new_pass){
		$new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

		$return_value = false;

		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "UPDATE `users` SET `password` = '$new_pass' WHERE `id` = $user_id;";
			$result = mysql_query($query);
			if ($result) {
				$return_value = true;
			}
			mysql_close($mysql);
		}				

		return $return_value;
	}
	/**
	 * @param int $user_id  			3
	 * @return array of type,handler	["email, "adrian@elementalgeeks.com", "facebook", "714395194"]
	 */
	public function accounts_associated_to_user ($user_id) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT account_type as type, account_handler as handler FROM accounts WHERE user_id=" . $user_id;
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_object($result)){
					array_push($return_value, $row->type);
					array_push($return_value, $row->handler);
				}				
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}	

	/**
	 * @param int $user_id  			3
	 * @return array of ints [$user_id,$user_id,$user_id]
	 */
	public function get_school_friends ($user_id) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

		$grado = $this->get_account_value($user_id,"grado");
		$seccion = $this->get_account_value($user_id,"seccion");
		$return_value = array();
		if ($grado != "" && $seccion != "") {
			$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
			if ($mysql) {
				mysql_select_db(DATABASE_NAME);
				$query = "SELECT user_id
						  FROM accounts 
						  WHERE account_type='grado' AND 
							    account_handler='$grado' AND 
								user_id IN (SELECT user_id 
										    FROM accounts 
										    WHERE account_type='seccion' 
											AND account_handler='$seccion'
											AND user_id <> $user_id)";
				$result = mysql_query($query);
				if ($result) {
					while ($row = mysql_fetch_object($result)){
						array_push($return_value, $row->user_id);
					}
					mysql_close($mysql);
				}
			}
		}
		return $return_value;		
	}
	
	/**
	 * @param int $user_id  			3
	 * @return array of ints [$user_id,$score,$user_id,$score,$user_id,$score]
	 */
	public function get_school_friends_with_scores ($user_id,$game_id=-1) {
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$friends = $this->get_school_friends($user_id);
		$return_value = array();

		foreach ($friends as $current_friend) {
			$score_from_db = $this->get_score ($current_friend, $game_id, 1);

			$current_score = 0;
			if (count($score_from_db) == 4) {
				$current_score = $score_from_db[3];
			}
			array_push($return_value, $current_friend);
			array_push($return_value, $current_score);
		}
		return $return_value;		
	}	
	
	/**
	 * @param int $user_id		123
	 * @param string $type		"facebook"
	 * @return string $handler	"714395194"
	 */
	public function get_account_value ($user_id, $type) {	
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);				
		$type = filter_var($type, FILTER_SANITIZE_STRING);		

		$return_value = "";
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);		
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT account_handler FROM accounts where account_type='$type' AND user_id=$user_id";

			$result = mysql_query($query);
			if ($result) {
				$row = mysql_fetch_object($result);
				if ($row) {					
					$return_value = $row->account_handler;
				}
				mysql_close($mysql);
			}
		}				
		return $return_value;		
	}
	/**
	* @param int $user_id
	* @return array $trophies list
	*/
	public function get_trophies($user_id){
		$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD,true);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT trophy_id, current_score, quota FROM  `trophies_earned` AS te, trophies AS t WHERE t.id = te.trophy_id AND user_id =$user_id";
			$result = mysql_query($query);
			if ($result) {
				if(sizeof($row)<2){
					$this->create_trophys($user_id);
				}
				while ($row = mysql_fetch_object($result)){
					$aux = $row->trophy_id;
					$aux1 = $row->current_score;
					$aux2 = $row->quota;
					array_push($return_value, $aux,$aux1,$aux2);
				}
			}
			//mysql_close($mysql);
		}
		return $return_value;
	}
	/**
	* @return array $trophies to games (trophy_id,game_id,trophy_id,game_id,...)
	*/
	public function trophies_x_games(){
		$return_value = array();
		$mysql = mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
		if ($mysql) {
			mysql_select_db(DATABASE_NAME);
			$query = "SELECT id,game_id FROM `trophies`";
			$result = mysql_query($query);
			if ($result) {
				while ($row = mysql_fetch_object($result)){
					$aux = $row->id;
					$aux1 = $row->game_id;
					array_push($return_value, $aux,$aux1);
				}
				mysql_close($mysql);
			}
		}
		return $return_value;
	}

}
?>