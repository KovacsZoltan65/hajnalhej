<?php

namespace Database\Seeders\test;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class OrderLoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $days=(int)env('HH_LOAD_TEST_DAYS',30);
        $start=Carbon::parse(env('HH_LOAD_TEST_START_DATE',now()->subDays($days)->toDateString()));
        $users=User::where('email','like','customer%@example.com')->get();
        $products=Product::all();

        for($d=0;$d<$days;$d++){
            $date=$start->copy()->addDays($d);
            $count=rand(2,7);
            $picked=$users->shuffle()->take($count);
            foreach($picked as $user){
                $target=rand(5000,8000);
                $total=0;
                $items=[];
                while($total < $target && $products->count()){
                    $p=$products->random();
                    $qty=rand(1,3);
                    $items[]=['product_id'=>$p->id,'quantity'=>$qty,'unit_price'=>$p->price];
                    $total += $p->price * $qty;
                }
                Order::create([
                    'user_id'=>$user->id,
                    'total'=>$total,
                    'status'=>'completed',
                    'created_at'=>$date->copy()->setHour(rand(6,18)),
                    'updated_at'=>$date,
                    'meta'=>json_encode(['items'=>$items]),
                ]);
            }
        }
    }
}
