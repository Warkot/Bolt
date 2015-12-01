<?php

class Tests {

	public static $GROUPS = [
		'Mercury' => [
			'MercuryArticleTests' => [
				'MercuryArticleTest_001',
				'MercuryArticleTest_002',
				'MercuryArticleTest_003',
				'MercuryArticleTest_004',
				'MercuryArticleTest_005'
			],
			'MercuryCommentsTests' => [
				'MercuryCommentsTest_001',
				'MercuryCommentsTest_002',
				'MercuryCommentsTest_003',
				'MercuryCommentsTest_004',
				'MercuryCommentsTest_005'
			],
			'MercuryCuratedContentTests' => [
				'MercuryCuratedEditorTests' => [
					'MercuryCuratedEditorTest_001',
					'MercuryCuratedEditorTest_002',
					'MercuryCuratedEditorTest_003'
				],
				'MercuryCuratedMainPageTests' => [
					'MercuryCuratedMainPageTest_001',
					'MercuryCuratedMainPageTest_002',
					'MercuryCuratedMainPageTest_003',
					'MercuryCuratedMainPageTest_004',
					'MercuryCuratedMainPageTest_005',
					'MercuryCuratedMainPageTest_006'
				],
				'MercuryCuratedNavigationTests' => [
					'MercuryCuratedNavigationTest_001',
					'MercuryCuratedNavigationTest_002',
					'MercuryCuratedNavigationTest_003',
					'MercuryCuratedNavigationTest_004'
				],
				'MercuryCuratedSectionItemsTests' => [
					'MercuryCuratedSectionItemsTest_001',
					'MercuryCuratedSectionItemsTest_002'
				]
			],
			'MercuryInteractiveMapsTests' => [
				'MercuryInteractiveMapsTest_001',
				'MercuryInteractiveMapsTest_002',
				'MercuryInteractiveMapsTest_003'
			],
			'MercuryLightboxTests' => [
				'MercuryLightboxTest_001',
				'MercuryLightboxTest_002',
				'MercuryLightboxTest_003',
				'MercuryLightboxTest_004',
				'MercuryLightboxTest_005',
				'MercuryLightboxTest_006'
			],
			'MercuryLoginTests' => [
				'MercuryLoginTest_001',
				'MercuryLoginTest_002',
				'MercuryLoginTest_003',
				'MercuryLoginTest_004',
				'MercuryLoginTest_005',
				'MercuryLoginTest_006',
				'MercuryLoginTest_007',
				'MercuryLoginTest_008',
				'MercuryLoginTest_009',
				'MercuryLoginTest_010',
				'MercuryLoginTest_011',
				'MercuryLoginTest_012',
			],
			'MercuryNavigationSideTests' => [
				'MercuryNavigationSideTest_001',
				'MercuryNavigationSideTest_002',
				'MercuryNavigationSideTest_003',
				'MercuryNavigationSideTest_004'
			],
			'MercurySEOTests' => [
				'MercurySEOTest_001'
			],
			'MercurySignupTests' => [
				'MercurySignupTest_001',
				'MercurySignupTest_002',
				'MercurySignupTest_003',
				'MercurySignupTest_004',
				'MercurySignupTest_005',
				'MercurySignupTest_006'
			],
			'MercurySmartBannerTests' => [
				'MercurySmartBannerTest_001',
				'MercurySmartBannerTest_002'
			],
			'MercuryTOCTests' => [
				'MercuryTOCTest_001',
				'MercuryTOCTest_002',
				'MercuryTOCTest_003',
				'MercuryTOCTest_004',
				'MercuryTOCTest_005',
				'MercuryTOCTest_006'
			],
			'MercuryWidgetTests' => [
				'MercuryAllTagsWidgetTests' => [
					'MercuryAllTagsWidgetTest_001',
					'MercuryAllTagsWidgetTest_002',
					'MercuryAllTagsWidgetTest_003'
				],
				'MercuryGoogleFormWidgetTests' => [
					'MercuryGoogleFormWidgetTest_001',
					'MercuryGoogleFormWidgetTest_002',
					'MercuryGoogleFormWidgetTest_003',
					'MercuryGoogleFormWidgetTest_004',
					'MercuryGoogleFormWidgetTest_005'
				],
				'MercuryPolldaddyWidgetTests' => [
					'MercuryPolldaddyWidgetTest_001',
					'MercuryPolldaddyWidgetTest_002',
					'MercuryPolldaddyWidgetTest_003',
					'MercuryPolldaddyWidgetTest_004',
					'MercuryPolldaddyWidgetTest_005',
					'MercuryPolldaddyWidgetTest_006'
				],
				'MercuryPollsnackWidgetTests' => [
					'MercuryPollsnackWidgetTest_001',
					'MercuryPollsnackWidgetTest_002',
					'MercuryPollsnackWidgetTest_003',
					'MercuryPollsnackWidgetTest_004',
					'MercuryPollsnackWidgetTest_005',
					'MercuryPollsnackWidgetTest_006'
				],
				'MercurySoundCloudWidgetTests' => [
					'MercurySoundCloudWidgetTest_001',
					'MercurySoundCloudWidgetTest_002',
					'MercurySoundCloudWidgetTest_003',
					'MercurySoundCloudWidgetTest_004'
				],
				'MercurySpotifyWidgetTests' => [
					'MercurySpotifyWidgetTest_001',
					'MercurySpotifyWidgetTest_002',
					'MercurySpotifyWidgetTest_003',
					'MercurySpotifyWidgetTest_004',
					'MercurySpotifyWidgetTest_005'
				],
				'MercuryTwitterWidgetTests' => [
					'MercuryTwitterWidgetTest_001',
					'MercuryTwitterWidgetTest_002',
					'MercuryTwitterWidgetTest_003',
					'MercuryTwitterWidgetTest_004',
					'MercuryTwitterWidgetTest_005'
				],
				'MercuryVKWidgetTests' => [
					'MercuryVKWidgetTest_001',
					'MercuryVKWidgetTest_002',
					'MercuryVKWidgetTest_003',
					'MercuryVKWidgetTest_004',
					'MercuryVKWidgetTest_005'
				],
				'MercuryWeiboWidgetTests' => [
					'MercuryWeiboWidgetTest_001',
					'MercuryWeiboWidgetTest_002',
					'MercuryWeiboWidgetTest_003',
					'MercuryWeiboWidgetTest_004',
					'MercuryWeiboWidgetTest_005'
				]
			]
		]
	];
}
