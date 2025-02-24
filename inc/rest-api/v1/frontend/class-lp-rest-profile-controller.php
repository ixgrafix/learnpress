<?php
class LP_REST_Profile_Controller extends LP_Abstract_REST_Controller {
	public function __construct() {
		$this->namespace = 'lp/v1';
		$this->rest_base = 'profile';

		parent::__construct();
	}

	public function register_routes() {
		$this->routes = array(
			'statistic'     => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'statistic' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			),
			'course-tab'    => array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'course_tab' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			),
			'course-attend' => array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'course_attend' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			),
		);

		parent::register_routes();
	}

	/**
	 * Check permission
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function check_permission( $request ): bool {
		$user_id = $request->get_param( 'userID' );

		if ( empty( $user_id ) ) {
			return false;
		}

		$profile = learn_press_get_profile( $user_id );

		if ( ! $profile->current_user_can( 'view-tab-courses' ) ) {
			return false;
		}

		return true;
	}

	public function statistic( WP_REST_Request $request ) {
		$user_id          = $request->get_param( 'userID' );
		$response         = new LP_REST_Response();
		$response->data   = '';
		$lp_user_items_db = LP_User_Items_DB::getInstance();

		try {
			if ( empty( $user_id ) ) {
				throw new Exception( esc_html__( 'No user ID found!', 'learnpress' ) );
			}

			$profile = learn_press_get_profile( $user_id );

			if ( $profile instanceof WP_Error ) {
				throw new Exception( $profile->get_error_message() );
			}

			/*$query = $profile->query_courses( 'purchased' );

			$counts = $query['counts'];*/

			// Count total courses has status 'in-progress'
			// $total_courses_has_status = $lp_user_items_db->get_total_courses_has_status( $user_id, 'in-progress' );

			$filter            = new LP_User_Items_Filter();
			$filter->user_id   = $user_id;
			$filter->item_type = LP_COURSE_CPT;
			$count_status      = $lp_user_items_db->count_status_by_items( $filter );

			$statistic = array(
				'enrolled_courses'  => $count_status->{LP_COURSE_PURCHASED} + $count_status->{LP_COURSE_ENROLLED} + $count_status->{LP_COURSE_FINISHED},
				'active_courses'    => $count_status->{LP_COURSE_GRADUATION_IN_PROGRESS},
				'completed_courses' => $count_status->{LP_COURSE_FINISHED} ?? 0,
				'total_courses'     => count_user_posts( $user_id, LP_COURSE_CPT ),
				'total_users'       => learn_press_count_instructor_users( $user_id ),
			);

			do_action( 'learnpress/rest/frontend/profile/statistic', $request );

			$response->data   = learn_press_get_template_content( 'profile/tabs/courses/general-statistic', compact( 'statistic' ) );
			$response->status = 'success';

		} catch ( Exception $e ) {
			$response->message = $e->getMessage();
		}

		return rest_ensure_response( $response );
	}

	public function course_tab( $request ) {
		$params     = $request->get_params();
		$user_id    = $params['userID'] ?? 0;
		$status     = $params['status'] ?? '';
		$paged      = $params['paged'] ?? 1;
		$query_type = $params['query'] ?? 'purchased';
		$layout     = $params['layout'] ?? 'grid';
		$response   = new LP_REST_Response();

		try {
			if ( empty( $user_id ) ) {
				throw new Exception( esc_html__( 'No user ID found!', 'learnpress' ) );
			}

			$profile = learn_press_get_profile( $user_id );

			$query = $profile->query_courses(
				$query_type,
				apply_filters(
					'learnpress/rest/frontend/profile/course_tab/query',
					array(
						'status' => $status,
						'limit'  => LP_Settings::get_option( 'archive_course_limit', 6 ),
						'paged'  => $paged,
					)
				)
			);

			// LP_User_Item_Course.
			$course_item_objects = ! empty( $query['items'] ) ? $query['items'] : false;

			if ( empty( $course_item_objects ) ) {
				throw new Exception( esc_html__( 'No Course available!', 'learnpress' ) );
			}

			$course_ids = array_map(
				function( $course_object ) {
					return ! is_object( $course_object ) ? absint( $course_object ) : $course_object->get_id();
				},
				$course_item_objects
			);

			if ( empty( $course_ids ) ) {
				throw new Exception( esc_html__( 'No Course IDs available!', 'learnpress' ) );
			}

			$user = learn_press_get_user( $user_id );

			if ( empty( $user ) ) {
				throw new Exception( esc_html__( 'No User available!', 'learnpress' ) );
			}

			do_action( 'learnpress/rest/frontend/profile/course_tab', $params );

			$num_pages    = $query->get_pages();
			$current_page = $query->get_paged();

			$template = $layout === 'grid' ? 'profile/tabs/courses/course-grid' : 'profile/tabs/courses/course-list';

			$response->data   = learn_press_get_template_content(
				$template,
				array(
					'user'         => $user,
					'course_ids'   => $course_ids,
					'num_pages'    => max( absint( $num_pages ), 1 ),
					'current_page' => absint( $current_page ),
				)
			);
			$response->status = 'success';

		} catch ( Exception $e ) {
			$response->message = $e->getMessage();
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get course's user attend
	 *
	 * @param WP_REST_Request $request
	 *
	 * @author tungnx
	 * @since 4.1.4.2
	 * @version 1.0.0
	 * @return LP_REST_Response
	 */
	public function course_attend( WP_REST_Request $request ): LP_REST_Response {
		$params   = $request->get_params();
		$user_id  = get_current_user_id();
		$status   = $params['status'] ?? '';
		$paged    = $params['paged'] ?? 1;
		$layout   = $params['layout'] ?? 'grid';
		$response = new LP_REST_Response();

		try {
			if ( ! $user_id ) {
				throw new Exception( __( 'User is invalid!', 'learnpress' ) );
			}

			$filter                      = new LP_User_Items_Filter();
			$filter->limit               = LP_Settings::get_option( 'archive_course_limit', 6 );
			$filter->user_id             = $user_id;
			$total_rows                  = 0;
			$courses                     = LP_User_Item_Course::get_user_courses( $filter, $total_rows );
			$response->data->courses     = $courses;
			$response->data->total_pages = LP_Database::get_total_pages( $filter->limit, $total_rows );
		} catch ( Throwable $e ) {
			$response->message = $e->getMessage();
		}

		return $response;
	}
}
