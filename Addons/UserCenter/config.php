<?php
return array (
		'need_truename' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '是否需要实名:', // 表单的文字
				'type' => 'radio', // 表单的类型：text、textarea、checkbox、radio、select等
				'options' => array ( // select 和radion、checkbox的子选项
						'1' => '是', // 值=>文字
						'0' => '否' 
				),
				'value' => '0'  // 表单的默认值
				),
		'need_mobile' => array ( // 配置在表单中的键名 ,这个会是config[random]
				'title' => '是否需要手机号:', // 表单的文字
				'type' => 'radio', // 表单的类型：text、textarea、checkbox、radio、select等
				'options' => array ( // select 和radion、checkbox的子选项
						'1' => '是', // 值=>文字
						'0' => '否' 
				),
				'value' => '0'  // 表单的默认值
				) 
);
					