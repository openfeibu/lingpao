<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use App\Models\UserAllCoupon;

class UserAllCouponRepository extends BaseRepository implements UserAllCouponRepositoryInterface
{

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.coupon.user_all_coupon.model');
    }
    public function useCoupon($user_id,$id,$total_price)
    {
        $coupon = $this->find($id);
        switch ($coupon->objective_model)
        {
            case 'UserCoupon':
                $user_coupon = app(UserCouponRepository::class)->getAvailableCoupon(['user_id' => $user_id,'id' => $coupon->objective_id],$total_price);

                return [
                    'price' => $user_coupon->price,
                    'name' => '满'.$user_coupon->min_price.'减'.$user_coupon->price,
                ];
                break;
            case 'UserBalanceCoupon':
                $user_balance_coupon = app(UserBalanceCouponRepository::class)->where(['user_id' => $user_id,'id' => $coupon->objective_id])->first();
                if(!$user_balance_coupon)
                {
                    throw new OutputServerMessageException('优惠券不存在');
                }
                if($user_balance_coupon->balance <= 0)
                {
                    throw new OutputServerMessageException('优惠券余额已为0');
                }
                $price = min([rid_two($total_price * floatval(setting('balance_coupon_rate'))),$user_balance_coupon->balance]);

                return [
                    'price' => $price,
                    'name' => '储蓄优惠券',
                ];
                break;
        }

    }
    public function usedCoupon($id,$total_price)
    {
        $coupon = $this->find($id);
        switch ($coupon->objective_model)
        {
            case 'UserCoupon':
                app(UserCouponRepository::class)->update(['status' => 'used'],$coupon->objective_id);
                break;
            case 'UserBalanceCoupon':
                $user_balance_coupon = app(UserBalanceCouponRepository::class)->find($coupon->objective_id);
                app(UserBalanceCouponRepository::class)->where(['id' => $coupon->objective_id])->updateData(['balance' => $user_balance_coupon->balance - $total_price]);
                break;
        }
    }
}
