namespace App\Http\Controllers\Battle;



use App\Model\Battle;
use App\Http\Controllers\Controller;




class BattleController extends Controller
{

	public function getBattle($battle_id)
	{
		
		return view('battle', ['battle' => Battle::find($battle_id)]);

	}

	
	public function getTrending()
	{


	}
	public function getOpenVoting()
	{


	}
	public function getCompleted()
	{


	}
	public function getOpen()
	{


	}
	public function postVote($battle_id)
	{


	}

}
