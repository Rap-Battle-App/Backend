namespace App\Http\Controllers\Battle;



use App\Model\Battle;
use App\Http\Controllers\Controller;
uae Illuminate\Http\Request;



class BattleController extends Controller
{

	public function __construct($AppName, IRequest $request){
         parent::__construct($AppName, $request);
    }

	public function getBattle($battle_id)
	{
		
		return view('battle', ['battle' => Battle::find($battle_id)]);

	}

	
	public function getTrending(Request $request)
	{
		return view('battle', ['battle' => Battle::scopeTrending($request)]);


	}
	public function getOpenVoting(Request $request)
	{
		return view('battle', ['battle' => Battle::scopeOpenVoting($request)]);

	}
	public function getCompleted()
	{

		/**
		*	missing function in Battle. Should be done via database query
		*/

	}
	public function getOpen()
	{
		/**
		*	get battles that are still in progress?
		*	is this the right place? What for? We have an own Controller for this
		*/

	}
	public function postVote($battle_id)
	{

		/**
		*	where is the function for raising the vote counter? Am I blind?
		*/
	}

}
