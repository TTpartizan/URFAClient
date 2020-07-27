<?php

include_once 'URFAClientBaseTest.php';

/**
 * @license https://github.com/k-shym/URFAClient/blob/master/LICENSE.md
 * @author  Konstantin Shum <k.shym@ya.ru>
 */
class URFAClient53Test extends URFAClientBaseTest {

    protected $_config = array(
        'login'    => 'init',
        'password' => 'init',
        'address'  => 'localhost',
        'protocol' => 'tls',
        'log'      => TRUE,
    );

    public function test_rpcf_get_discount_periods()
    {
        $result = $this->_api->rpcf_get_discount_periods();

        $this->assertArrayHasKey('discount_periods_count', $result);
        $this->assertTrue((bool) count($result['discount_periods_count']));

        return $result['discount_periods_count'];
    }

    public function test_rpcf_add_user_new()
    {
        $result = $this->_api->rpcf_add_user_new(array(
            'login'    => 'user' . self::prefix(),
            'password' => 'pass' . self::prefix(),
        ));

        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('basic_account', $result);

        return $result;
    }

    /**
     * @depends test_rpcf_add_user_new
     */
    public function test_rpcf_get_userinfo(array $user)
    {
        $result = $this->_api->rpcf_get_userinfo(array(
            'user_id' => $user['user_id'],
        ));

        $this->assertTrue((bool) $result);
        $this->assertEquals($user['user_id'], $result['user_id']);
        $this->assertEquals($user['basic_account'], $result['basic_account']);
        $this->assertEquals('user' . self::prefix(), $result['login']);
        $this->assertEquals('pass' . self::prefix(), $result['password']);
    }

    /**
     * @depends test_rpcf_add_user_new
     */
    public function test_rpcf_search_users_new(array $user)
    {
        $result = $this->_api->rpcf_search_users_new(array(
            'select_type'    => 0,
            'patterns_count' => array(
                array(
                    'what'        => 2,
                    'criteria_id' => 3,
                    'pattern'     => 'user' . self::prefix(),
                ),
            ),
        ));

        $this->assertArrayHasKey('user_data_size', $result);
        $this->assertTrue(count($result['user_data_size']) === 1);
        $result = $result['user_data_size'][0];
        $this->assertEquals($user['user_id'], $result['user_id']);
        $this->assertEquals($user['basic_account'], $result['basic_account']);
        $this->assertEquals('user' . self::prefix(), $result['login']);
    }

    /**
     * @depends test_rpcf_add_user_new
     * @throws Exception
     */
    public function test_init_api_user()
    {
        $this->_config['login'] = 'user' . self::prefix();
        $this->_config['password'] = 'pass' . self::prefix();
        $this->_config['admin'] = FALSE;
        $api_user = URFAClient::init($this->_config);

        $this->assertInstanceOf('URFAClient_Collector', $api_user);

        return $api_user;
    }

    /**
     * @depends test_init_api_user
     */
    public function test_rpcf_user5_change_password(URFAClient_Collector $api)
    {
         $result = $api->rpcf_user5_change_password(array(
            'old_password'     => 'pass' . self::prefix(),
            'new_password'     => 'pass' . self::prefix(),
            'new_password_ret' => 'pass' . self::prefix(),
        ));

        $this->assertTrue($result['result'] > 0);
    }

    /**
     * @depends test_init_api_user
     */
    public function test_rpcf_user5_edit_user(URFAClient_Collector $api)
    {
        $data = array(
            'full_name'         => 'full_name' . self::prefix(),
            'actual_address'    => 'actual_address' . self::prefix(),
            'juridical_address' => 'juridical_address' . self::prefix(),
            'work_telephone'    => 'work_telephone' . self::prefix(),
            'home_telephone'    => 'home_telephone' . self::prefix(),
            'mobile_telephone'  => 'mobile_telephone' . self::prefix(),
            'web_page'          => 'web_page' . self::prefix(),
            'icq_number'        => 'icq_number' . self::prefix(),
            'pasport'           => 'pasport' . self::prefix(),
            'bank_id'           => 0,
            'bank_account'      => 'bank_account' . self::prefix(),
            'email'             => 'email' . self::prefix(),
        );

        $api->rpcf_user5_edit_user($data);
        $result = $api->rpcf_user5_get_user_info_new();

        foreach ($data as $k => $v) $this->assertEquals($result[$k], $v);
    }

    /**
     * @depends test_rpcf_add_user_new
     */
    public function test_rpcf_save_user_othersets(array $user)
    {
        $this->assertTrue(is_array($this->_api->rpcf_save_user_othersets(array(
            'user_id' => $user['user_id'],
            'count'   => array(
                array(
                    'type'        => 3,
                    'currency_id' => 978,
                ),
            ),
        ))));
    }

    public function test_rpcf_add_iptraffic_service_ex()
    {
        $result = $this->_api->rpcf_add_iptraffic_service_ex(array(
            'parent_id'            => 0,
            'tariff_id'            => 0,
            'service_name'         => 'service' . self::prefix(),
            'comment'              => 'Тестовая услуга',
            'link_by_default'      => 0,
            'is_dynamic'           => 0,
            'cost'                 => 0.13,
            'discount_method'      => 1,
            'sessions_limit'       => 0,
            'null_service_prepaid' => 0,
        ));

        $this->assertArrayHasKey('service_id', $result);
        $this->assertTrue($result['service_id'] > 0);

        return $result;
    }

    /**
     * @depends test_rpcf_add_iptraffic_service_ex
     */
    public function test_rpcf_get_iptraffic_service_ex(array $service)
    {
        $result = $this->_api->rpcf_get_iptraffic_service_ex(array(
            'sid' => $service['service_id'],
        ));

        $this->assertTrue((bool) $result);
        $this->assertEquals('service' . self::prefix(), $result['service_name']);
        $this->assertEquals('Тестовая услуга', $result['comment']);
        $this->assertEquals(1, $result['discount_method']);
    }

    /**
     * @depends test_rpcf_add_user_new
     * @depends test_rpcf_add_iptraffic_service_ex
     * @depends test_rpcf_get_discount_periods
     */
    public function test_rpcf_add_iptraffic_service_link_ipv6(array $user, array $service, array $discount_periods)
    {
        $discount_period = array_pop($discount_periods);

        $result = $this->_api->rpcf_add_iptraffic_service_link_ipv6(array(
            'user_id'            => $user['user_id'],
            'account_id'         => $user['basic_account'],
            'service_id'         => $service['service_id'],
            'tplink_id'          => 0,
            'discount_period_id' => $discount_period['discount_period_id'],
            'start_date'         => time(),
            'expire_date'        => 2000000000,
            'policy_id'          => 1,
            'unabon'             => 0,
            'unprepay'           => 0,
            'ip_groups_count'    => array(
                array(
                    'ip'             => long2ip(rand()),
                    'mask'           => 32,
                    'mac'            => '',
                    'login'          => 'inet4user' . self::prefix(),
                    'allowed_cid'    => '',
                    'password'       => 'inet4pass' . self::prefix(),
                    'is_skip_radius' => 0,
                    'is_skip_rfw'    => 0,
                    'router_id'      => 0,
                ),
                array(
                    'ip'             => implode(':', str_split(md5(rand()), 4)),
                    'mask'           => 32,
                    'mac'            => '',
                    'login'          => 'inet6user' . self::prefix(),
                    'allowed_cid'    => '',
                    'password'       => 'inet6pass' . self::prefix(),
                    'is_skip_radius' => 0,
                    'is_skip_rfw'    => 0,
                    'router_id'      => 0,
                ),
            ),
        ));

        $this->assertArrayHasKey('slink_id', $result);
        $this->assertTrue($result['slink_id'] > 0);

        return $result;
    }

    /**
     * @depends test_rpcf_add_iptraffic_service_link_ipv6
     * @depends test_rpcf_get_discount_periods
     */
    public function test_rpcf_get_iptraffic_service_link_ipv6(array $slink, array $discount_periods)
    {
        $result = $this->_api->rpcf_get_iptraffic_service_link_ipv6(array(
            'slink_id' => $slink['slink_id'],
        ));

        $discount_period = array_pop($discount_periods);

        $this->assertTrue((bool) $result);
        $this->assertEquals($discount_period['discount_period_id'], $result['discount_period_id']);
        $this->assertEquals(2000000000, $result['expire_date']);
        $this->assertTrue(is_array($result['ip_groups_count']));

        foreach ($result['ip_groups_count'] as $ip_group)
        {
            $this->assertTrue((bool) filter_var($ip_group['ip_address'], FILTER_VALIDATE_IP));
            $this->assertEquals(32, $ip_group['mask']);
        }
    }

    /**
     * @depends test_rpcf_add_iptraffic_service_link_ipv6
     */
    public function test_rpcf_set_radius_attr(array $slink)
    {
        $radius_attrs = array(
            array(
                'vendor'      => 100000,
                'attr'        => 1,
                'usage_flags' => 1,
                'param1'      => 1,
                'cval'        => 'c102400',
            ),
        );

        $this->_api->rpcf_set_radius_attr(array(
            'sid' => $slink['slink_id'],
            'st'  => 10000,
            'cnt' => $radius_attrs,
        ));

        $result = $this->_api->rpcf_get_radius_attr(array(
            'sid' => $slink['slink_id'],
            'st'  => 10000,
        ));

        $this->assertTrue(count($result['radius_data_size']) === count($radius_attrs));
        foreach ($result['radius_data_size'] as $k => $v)
        {
            $this->assertTrue($v['vendor'] === $radius_attrs[$k]['vendor']);
            $this->assertTrue($v['attr'] === $radius_attrs[$k]['attr']);
            $this->assertTrue($v['usage_flags'] === $radius_attrs[$k]['usage_flags']);
            $this->assertTrue($v['param1'] === $radius_attrs[$k]['param1']);
            $this->assertTrue($v['val'] === $radius_attrs[$k]['cval']);
        }
    }

    /**
     * @depends test_rpcf_add_user_new
     * @depends test_rpcf_add_iptraffic_service_ex
     * @depends test_rpcf_get_discount_periods
     */
    public function test_rpcf_add_iptraffic_service_link_ipv6_without_ip(array $user, array $service, array $discount_periods)
    {
        $discount_period = array_pop($discount_periods);

        $result = $this->_api->rpcf_add_iptraffic_service_link_ipv6(array(
            'user_id'            => $user['user_id'],
            'account_id'         => $user['basic_account'],
            'service_id'         => $service['service_id'],
            'tplink_id'          => 0,
            'discount_period_id' => $discount_period['discount_period_id'],
            'start_date'         => time(),
            'expire_date'        => strtotime('+1 month'),
            'policy_id'          => 1,
            'unabon'             => 0,
            'unprepay'           => 0,
        ));

        $this->assertArrayHasKey('slink_id', $result);
        $this->assertTrue($result['slink_id'] === -1);
    }

    /**
     * @dataProvider long
     */
    public function test_rpcf_add_fwrule_new($long)
    {
        $result = $this->_api->rpcf_add_fwrule_new(array(
            'flags'     => 0,
            'events'    => $long,
            'router_id' => 0,
            'tariff_id' => 0,
            'group_id'  => 0,
            'user_id'   => 0,
            'rule'      => 'ACCOUNT_ID',
            'comment'   => '',
        ));

        $this->assertArrayHasKey('rule_id', $result);
        $this->assertTrue($result['rule_id'] > 0);

        $rule_id = $result['rule_id'];

        $result = $this->_api->rpcf_get_fwrules_list_new();

        $this->assertArrayHasKey('rules_count', $result);

        foreach ($result['rules_count'] as $v)
            if ($v['rule_id'] == $rule_id)
                $this->assertEquals($long, $v['events']);
    }

    public function long()
    {
        return array(
          array('-922334069862591'),
          array(-9013),
          array(9013),
          array('922334069862591')
        );
    }

    public function test_rpcf_get_userinfo_not_user()
    {
        $this->assertFalse((bool) $this->_api->rpcf_get_userinfo(array(
            'user_id' => 0,
        )));
    }
}
