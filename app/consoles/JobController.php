<?php
namespace app\consoles;

use ysf\console\Controller;
use ysf\Ysf;

class JobController extends Controller
{
    /**
     * php console.php  /job/task "params1" "params2"
     */
    public function actionTask($param1, $param2)
    {
        for ($i = 0; $i < 10; $i++){
            Ysf::trace("traceLog=".$i);
            sleep(1);
        }
        echo "this is job task, p1=$param1, p2=$param2";
    }
}