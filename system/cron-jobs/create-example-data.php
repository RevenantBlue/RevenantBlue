<?php

session_start();

$_SESSION['userId'] = 2;
// Since this script is called from the command line
// we need to set the directories so we can load our configuration and mail-queue object.

$webRoot = dirname(dirname(dirname(__FILE__)));
$cwd = dirname(dirname(__FILE__));

require_once($webRoot . '/config.php');
require_once(DIR_DATABASE . 'db.php');
require_once(DIR_DATABASE . 'redis.php');
require_once(DIR_ADMIN . 'model/config/config-main.php');
require_once(DIR_SYSTEM . 'startup.php');
require_once(DIR_SYSTEM . 'engine/acl.php');
require_once(DIR_ADMIN . 'model/users/users-main.php');
require_once(DIR_ADMIN . 'model/articles/articles-main.php');
require_once(DIR_APPLICATION . 'model/forums/forums-main.php');
$acl = new ACL;


buildTestForum(60);
//insertFakeArticles(40);

function buildTestForum($numOfTopics) {
	$forums = new Forums;
	$users = new admin\Users;
	$redis = new RedisCommand;
	
	require_once(DIR_APPLICATION . 'controller/forums/forum-post-validation.php');
	
	$user = $users->getUserData(2);
	
	$titles = array(
		'Proactively harness distinctive action items'
	  , 'Credibly simplify holistic innovation'
	  , 'Synergistically fabricate out-of-the-box opportunities'
	  , 'Efficiently evisculate bleeding-edge growth strategies'
	  , 'Fungibly restore installed base paradigms'
	  , 'Continually mesh accurate systems'
	  , 'Fungibly mesh extensible clouds'
	  , 'Monotonectally strategize 24/7 quality vectors'
	  , 'Dramatically fabricate customer directed methods of empowerment'
	  , 'Interactively reconceptualize premium resources'
	  , 'Objectively plagiarize cross functional metrics'
	  , 'Intrinsicly envisioneer distinctive meta-services'
	  , 'Collaboratively maintain go forward synergy'
	  , 'Rapidiously re-engineer ubiquitous nosql'
	  , 'Progressively leverage other\'s functional communities'
	  , 'Credibly strategize bleeding-edge results'
	  , 'Interactively cloudify wireless systems'
	  , 'Compellingly envisioneer sustainable e-business'
	  , 'Aggregate best-of-breed action-items'
	  , 'Reinvent impactful web-readiness'
	);
	
	$bullshit = array(
		'The clients adequately standardize a business line by expanding boundaries. The gatekeeper strategically differentiates a dynamic leadership. Issue and adaptability synergize a solution provider, whilst movable correlations facilitate a goal-directed, target, intuitiveness. A scale-as-you-grow benefit accelerates a superior Management Information System at the individual, team and organizational level. Our informationalization synergizes the resources.'
	  , 'Our paradigm shifts drive the customers. Enabler and SWOT analysis transfer improved win-win solutions, while our solid concept targets the powerful champion in the marketplace. The resource broadens a tolerably expensive footprint using the informed levers.'
	  , 'The clients manage a project. The Chief IT Operations Officer broadens a specific emotional intelligence.'
	  , 'The partners visualize a sales target, whilst the powerful champion identifies problems. A customer experience strengthens the business leaders. The compatible channels architect integrated objectives. As a result, our aligned say/do ratios seamlessly enable the enablers. The sales manager connects the dots to the end game, while the enabler drives multi-channel measurements. The Chief Digital Officer adapts a customized best practice. A top-level benchmarking drives the stakeholders. Our versatile and customer-centric cost efficiency facilitates future controls. The resources expediently right-size high-margin baseline starting points, whereas the steering committee seamlessly strategizes the touchpoints. The key people improve projects. Guideline and resourcefulness standardize a cultural, effective, strategy formulation.'
	  , 'The standard-setters mitigate unknown unknowns by nurturing talent, whereas best-of-breed strategy formulations streamline the educated, goal-oriented, credibilities.'
	  , 'Our cooperative efficiencies enable the brand identities champion across and beyond the silos.'
	  , 'The clients integrate our reliable benefits. The key people diligentlydo things differently up-front. Differentiator, informationalization and business line organically foster a customer-centric Management Information System, while a key framework expediently facilitates our outsourced change. Established enhanced data captures synergize the clients. Personalized and carefully thought-out success factors impact our established lever, whereas the powerful champion organically synergizes an upside focus. The customers benchmark our executive, vision-setting, goal. In the same time, the reporting unit should 24/7 drive the business forward. A multi-source successful execution structures above-average paradigm shifts.'
	  , 'A teamwork transfers the project manager. Our progressive Control Information Systems transfer the enablers. As a result, the business leaders conservatively flesh out our active respect. Pillars drive a streamlined, differentiating, ROE as a consequence of proven improvement. The community reaches out our functional and alert relationship.'
	  , 'Our accurate openness strengthens the compliance champion, while the enabler manages our credible footprints. An outstanding, competent and holistic strategic thinking boosts game-changing, market-driven and executive-level style guidelines 50/50, while the hyper-hybrid, outward-looking and underlying self-efficacy inspires the support structures champion.'
	  , 'Next-level and/or future-ready escalations synergize the group; this is why the resources establish the trusted line-up.'
	  , 'The team players promote an adequate case study. The partners strengthen cross-functional win-win solutions.'
	  , 'The human resources enhance our non-linear, specific, mindsets.'
	  , 'A seamless trust enables the enabler, whereas an outsourced and centralized leadership strategy influences the project manager. The high-performing landscapes enhance the big picture.'
	  , 'Our gut-feeling is that the standard-setters consistently establish measured yield enhancement.'
	  , 'Feedback-based synergies impact our balanced branding strategy, while the steering committee fleshes out situational, customer-centric, scalings across our portfolio. The key people reach out insights. A solutions-based core competency leverages a platform by thinking and acting beyond boundaries. Tactics efficiently strengthen the human resources up, down and across the matrix.'
	  , 'Cascading operating strategies prioritize our present-day and rock-solid core meeting. As a result, our pyramids incentivise the clients.'
	  , 'We need to incentivize a consistent and specific implementation.'
	  , 'We must activate the matrix to seamlessly transition movable, coordinated, workshops.'
	  , 'The next step champion achieves efficiencies.'
	  , 'Generic and low-risk high-yield Quality Management Systems cautiously promote our enterprise-wide atmosphere. The customers deepen non-standard branding strategies. The stakeholders champion an efficient frontier because our decentralized and carefully thought-out portal produces breakout improvement. In the same time, the resources strengthen multi-source missions. The sales manager reaches out a global decision making; nevertheless our well-crafted gamification enables the partners by leveraging promising, present-day, sign-offs.'
	  , 'A strategy prioritizes a case study by leveraging consistencies; this is why the business leaders facilitate efficient frontiers. The standard-setters benchmark a top-level emotional impact; this is why the consistency generates our executive, new, workshop. The customers pre-prepare our prospective recognition.'
	  , 'A goal motivates the Chief Controlling Officer, while informed shareholder values enable our relevant uniformity.'
	  , 'The service-oriented action plan strengthens the sales manager. The future, rock-solid, image technically standardizes a low-risk high-yield, potential and enterprise-wide scoping, while a well-communicated consistency interactively inspires the business leaders. The Chief Technical Officer strategically avoids unfavorable developments. Changes empower the senior support staff on-the-fly. The sales manager credibly institutionalizes the insights, while our transitional openness significantly enables on-message and/or optimal pillars.'
	  , 'The long-term integrations transfer our enabler, while our in-depth cross fertilization accelerates credibilities. A kick-off facilitates a future-ready portal.'
	  , 'The resources efficiently generate an organic growth reaped from our organic growth, while the market-driven rewards 200% promote the group. The thought leader carefully differentiates generic sales targets by thinking outside of the box, while a selective enterprise risk management enables baseline starting points. Competent blended approaches enforce adaptive, carefully thought-out and siloed say/do ratios up-front. Pursuing this route will enable us to deploy on-boarding processes by expanding boundaries.'
	  , 'Pursuing this route will enable us to integrate challenging insights. Our differentiating relationships synergize the senior support staff. In the same time, our contents deepen our business enabling portal. The steering committee rebalances a mission. ROI and capability inspire the customers, while our forward-looking, solid, flow chartings standardize cross-functional, documented, pre-plans. The group fleshes out our feedback-based pre-plans as part of the plan, while the resource quickly deploys measured throughput increase. The stakeholders influence on-message and/or time-honored performances. The partners innovate a collaboration. The human resources manage the cooperative empowerments, while our effective client focus standardizes the executive, heart-of-the-business, accurate and challenging objectives.'
	  , 'Our lessons learned carefully generate top-down accomplishments; this is why the intra-organisational values swiftly enable the gatekeeper. The Chief of IT Strategy connects the dots to the end game. The business leaders transition a cascading implication. Our underlying scaling strengthens a nimble, competent, plan. Our holistic and centralized relationships standardize omni-channel idiosyncrasies, whereas an action plan leverages the review cycles. Enhanced data capture and idiosyncrasy globally enable the stakeholders. We are working hard to globally embrace cross-industry Balanced Scorecards. Unique, non-deterministic, large-scale and established say/do ratios quickly drive the human resources. The human resources execute on priorities.'
	  , 'The resources conservatively go forward together.'
	  , 'The powerful champion technically makes things happen, while the streamlined documents influence the Chief Internal Audit Officer. The gatekeeper benchmarks unique sales targets; nevertheless portfolio shaping, Quality Research and win-win solution leverage a top-down, aggressive, full range of products resulting in measured growth. Our gut-feeling is that the standard-setters institutionalize the upper single-digit growth by thinking outside of the box.'
	  , 'The team players adequately strengthen our case study. Pursuing this route will enable us to drive our high-performing core competency; this is why accountability, planning granularity and white paper inspire the Chief Visionary Officer. The Chief Client Relationship Officer structures messages, while the Chief Management Office Officer interactively takes a bite out of the interconnected and one-to-one team building taking advantage of a macroscopic core business.'
	  , 'The clients rebalance our sustainable, heart-of-the-business, business model, whereas growing touchpoints drive the partners. Our 360-degree decisions influence the clients, while the group establishes market conditions on-the-fly.'
	  , 'The partners manage the balance.'
	  , 'The Managing Senior Executive Director of IT Operations strengthens a high-quality, state-of-the-art, forward planning, whilst the Chief Client Relationship Officer optimizes our medium-to-long-term and carefully thought-out branding within the industry. The cutting-edge structures generate a risk management. Our win-win solutions enable the human resources. The clients formulate our market opportunities; nevertheless the enablers transition our profit-maximizing Quality Research in the marketplace. A carefully thought-out big-picture thinking drives our support structures.'
	  , 'The human resources avoid barriers, whilst the partners significantly target our 360-degree asset. Risk appetite, self-efficacy and executive talent enable brand identities.'
	  , 'The Chief Controlling Officer facilitates a value-enhancing, compatible and carefully thought-out responsibility, whereas the partners carefully avoid our surprises.'
	  , 'A proven yield enhancement empowers the stakeholders.'
	  , 'The thought leader institutionalizes multi-source measures. Competent assets motivate the human resources. Our learnings interact with our projections. The standard-setters genuinely embrace a parallel portfolio shaping at the individual, team and organizational level. Perspectives prioritize the Managing Chief of Management Office.'
	  , 'The thought leader significantly standardizes adaptive performances at the individual, team and organizational level, while the Chief Human Resources Officer interactively builds an one-to-one, problem-solving and client-oriented priority.'
	  , 'The group increases customer satisfaction. In the same time, a productive talent retention leverages our profit-maximizing risk/return profiles.'
	  , 'Time-phased style guidelines adequately influence the profit-maximizing diversification. The customers engineer on-boarding processes, while a traceable win-win solution synergizes in-depth transformation processes.'
	  , 'The stakeholders learn a sign-off.'
	  , 'Controlling should transition an unique, inspiring and sustainable correlation on a transitional basis. Our double-digit growth enhances a systematized impetus. The effective pipeline cautiously transfers the mindsets champion from the get-go.'
	  , 'The enablers transition the consistent atmosphere, while the sales manager loops back.'
	  , 'The gatekeeper fleshes out a verifiable and/or educated best practice by leveraging a leveraged next step; this is why the stakeholders significantly streamline an organizational and/or awesome feedback up, down and across the sphere.'
	  , 'Our outside-in, alert, interactive and movable strategic staircase strengthens our enhanced and constructive Balanced Scorecards; nevertheless the steering committee empowers an intelligent next step going forward. The stakeholders leverage a carefully thought-out upside focus in the marketplace, while the resource accelerates fact-based cost savings.'
	  , 'An aggressive knowledge transfer technically deepens our non-standard innovativeness. The verifiable, enterprise-wide and agile white paper strengthens non-standard concepts, whilst industry-standard benchmarks culturally strengthen the clients.'
	  , 'Our credibilities interact with an accurate and market-altering communication within the silo; this is why a vision-setting and holistic pipeline enables the key people within the industry. Our internal clients generate systematized engagements. A nimble and top-level open-door policy influences the sales manager.'
	);
	
	for($x = 1; $x <= $numOfTopics; $x++) {
		$topic['title'] = $titles[array_rand($titles, 1)];
		$topic['content'] = $bullshit[array_rand($bullshit, 1)];
		$topic['userId'] = $user['id'];
		$topic['username'] = $user['username'];
		$topic['published'] = 1;
		$topic['forumId'] = 2;
		$forumPostValidation = new ForumPostValidation($topic, TRUE);
		
		// Insert the topic.
		$newTopicId = $forums->insertTopic($forumPostValidation->topic);
		
		// A new topic is also the first reply.
		$newPostId = $forums->insertPost($forumPostValidation->topic);
		
		// Increment the number of topics/posts for the forum and topic.
		$forums->incrementNumOfTopicsForForum($forumPostValidation->topic['forumId']);
		$forums->incrementNumOfPostsForForum($forumPostValidation->topic['forumId']);
		
		$dateOfLastPost = $forums->getDateForPost($newPostId);
		
		// Update the last post information for the forum.
		$forums->setForumLastPostUser(
			$forumPostValidation->forumId
		  , $forumPostValidation->userId
		  , $forumPostValidation->username
		  , $forumPostValidation->usernameAlias
		  , $newTopicId
		  , $dateOfLastPost
		);
		
		// Set the last post info for the topic.
		$forums->setTopicLastPostUser(
			$forumPostValidation->topicId
		  , $forumPostValidation->userId
		  , $forumPostValidation->username
		  , $forumPostValidation->usernameAlias
		  , $dateOfLastPost
		);

		// Increment the user's post count by one.
		if(REDIS === TRUE) {
			$currentPostCount = $redis->get(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount');
			if(!empty($currentPostCount)) {
				$redis->set(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount', (int)$currentPostCount + 1);
			} else {
				$redis->set(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount', 1);
			}
		}
		
		$users->incrementForumPostCount($_SESSION['userId']);
	}
	
	$topics = $forums->getTopics();
	
	foreach($topics as $topic) {
		
		$numOfBSPosts = mt_rand('8', '65');
		
		for($x = 1; $x <= $numOfBSPosts; $x++) {
			
			$post['topicId'] = $topic['id'];
			$post['forumId'] = $topic['forum_id'];
			$post['topicAlias'] = $topic['topic_alias'];
			$post['content'] = $bullshit[array_rand($bullshit, 1)];
			$post['published'] = 1;
			$post['userId'] = $user['id'];
			$post['username'] = $user['username'];
			
			$forumPostValidation = new ForumPostValidation($post);
			
			$newPostId = $forums->insertPost($forumPostValidation->post);
			
			// Increment the number of posts for the forum and topic.
			$forums->incrementNumOfPostsForForum($forumPostValidation->post['forumId']);
			$forums->incrementNumOfPostsForTopic($forumPostValidation->post['topicId']);
			
			// Increment the cached user's post count by one.
			if(REDIS === TRUE) {
				$currentPostCount = $redis->get(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount');
				if(!empty($currentPostCount)) {
					$redis->set(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount', (int)$currentPostCount + 1);
				} else {
					$redis->set(PREFIX . 'user:' . $_SESSION['userId'] . ':forumPostCount', 1);
				}
			}
			// Increment the database user's posts by one.
			$users->incrementForumPostCount($_SESSION['userId']);
			
			$dateOfLastPost = $forums->getDateForPost($newPostId);
			
			// Update the last post information for the forum.
			$forums->setForumLastPostUser(
				$forumPostValidation->forumId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $forumPostValidation->post['topicId']
			  , $dateOfLastPost
			);
			// Set the last post info for the topic.
			$forums->setTopicLastPostUser(
				$forumPostValidation->topicId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $dateOfLastPost
			);
		}
	}
}

function insertFakeArticles($numOfArticles) {
	
	$articles = new Articles;
	
	require_once(DIR_ADMIN . 'controller/articles/article-validation.php');
	
	$titles = array(
		'Proactively harness distinctive action items'
	  , 'Credibly simplify holistic innovation'
	  , 'Synergistically fabricate out-of-the-box opportunities'
	  , 'Efficiently evisculate bleeding-edge growth strategies'
	  , 'Fungibly restore installed base paradigms'
	  , 'Continually mesh accurate systems'
	  , 'Fungibly mesh extensible clouds'
	  , 'Monotonectally strategize 24/7 quality vectors'
	  , 'Dramatically fabricate customer directed methods of empowerment'
	  , 'Interactively reconceptualize premium resources'
	  , 'Objectively plagiarize cross functional metrics'
	  , 'Intrinsicly envisioneer distinctive meta-services'
	  , 'Collaboratively maintain go forward synergy'
	  , 'Rapidiously re-engineer ubiquitous nosql'
	  , 'Progressively leverage other\'s functional communities'
	  , 'Credibly strategize bleeding-edge results'
	  , 'Interactively cloudify wireless systems'
	  , 'Compellingly envisioneer sustainable e-business'
	  , 'Aggregate best-of-breed action-items'
	  , 'Reinvent impactful web-readiness'
	);
	
	for($x = 0; $x <= $numOfArticles; $x++) {
		
		$articleValidation = new ArticleValidation;
		
		$article['author'] = 2;
		$article['title'] = $titles[array_rand($titles)];
		$article['alias'] = $articleValidation->validateAlias($article['title'], TRUE);
		$article['image'] = 'placeholder.png';
		$article['imageAlt'] = 'Placeholder';
		$article['content'] = nl2br("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sit amet enim quis arcu ultrices interdum ut id metus. Nullam dignissim erat dolor, molestie condimentum est dapibus id. Donec diam metus, bibendum vel adipiscing at, blandit ac sapien. Nam a metus ultricies, tempor tortor in, gravida ante. Fusce quis mauris a felis consectetur sodales varius nec ligula. Donec ultrices enim non semper lobortis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent ipsum nibh, mattis sed est ac, sagittis tempor turpis. Donec nec leo ut urna rhoncus ullamcorper. Phasellus sed mauris luctus, aliquet nulla sit amet, lacinia nisi. Fusce feugiat felis neque, nec hendrerit sem ultricies vel. Donec eu justo quis nisi tristique egestas. In bibendum, enim ut bibendum aliquam, turpis felis gravida leo, ac sollicitudin risus dui aliquet tortor. Nullam odio nulla, rhoncus sit amet elit non, placerat pellentesque nulla.
\n\nDonec tincidunt bibendum elit, sit amet pharetra erat ullamcorper viverra. Vivamus in neque dui. Cras lacinia egestas tristique. Duis quis tincidunt nibh, in elementum lectus. Quisque massa sem, ornare ac orci sed, scelerisque faucibus metus. Proin semper risus sapien, sed ultricies turpis congue eget. Ut ultrices tortor dolor, eget malesuada lorem commodo vel. Donec consequat justo ligula, eget cursus tellus feugiat et. Morbi et facilisis velit. Duis blandit eu ante a suscipit. Pellentesque facilisis massa augue, id euismod felis auctor et. Cras accumsan dui diam, vel faucibus orci ultricies non. Aliquam sit amet diam iaculis, condimentum velit molestie, pharetra tellus.
\n\nMaecenas semper tortor vitae volutpat pellentesque. Nulla eleifend, massa eget semper posuere, ante lorem feugiat est, sagittis vulputate libero quam et dui. Nulla vel cursus augue. Vivamus tincidunt metus enim, vel porttitor tellus cursus quis. Nam nec odio et nisi accumsan porttitor. Pellentesque bibendum risus id est semper, at luctus mi viverra. Nulla tempus congue semper. Suspendisse a velit scelerisque, euismod diam vitae, aliquam ipsum. Suspendisse tempus magna id bibendum pellentesque. Vestibulum lacinia odio sed porta mattis.");
		$article['summary'] = "Fusce aliquam ligula vitae lobortis viverra. Curabitur nec justo vitae odio scelerisque sagittis vel eu urna. Nullam vestibulum felis a placerat varius. Sed ultricies metus et eleifend tristique. Integer interdum porta sem, ullamcorper vestibulum sapien. Aenean sagittis molestie lectus, vitae rutrum massa varius eu. Quisque dignissim feugiat felis, sed eleifend ipsum accumsan non. Duis convallis mi eros, vel imperdiet felis porta in.";
		$article['categories'] = array('2');
		$article['datePosted'] = date("Y-m-d H:i:s", time());
		$article['published'] = 1;
		$article['featured'] = 0;
		$article['metaDescription'] = '';
		$article['metaKeywords'] = '';
		$article['metaAuthor'] = '';
		$article['metaRobots'] = '';
		$article['weight'] = 1;

		$newArticleId = $articles->insertArticle($article);
		
		foreach($article['categories'] as $categoryId) {
			$insertCategory[] = $articles->insertArticleCategory($newArticleId, $categoryId);
		}
		sleep(2);
	}
}
