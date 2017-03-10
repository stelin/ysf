<?php
namespace app\consoles;

use ysf\console\Controller;

class JobController extends Controller
{
    /**
     * php console.php  /job/task "params1" "params2"
     */
    public function actionTask($param1, $param2)
    {
        echo "this is job task, p1=$param1, p2=$param2";
    }
}