<?php
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

/*if($client->isAccessTokenExpired()) {

  $authUrl = $client->createAuthUrl();
  header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));

}*/

// If the user has already authorized this app then get an access token
// else redirect to ask the user to authorize access to Google Analytics.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  // Set the access token on the client.
  $client->setAccessToken($_SESSION['access_token']);

  // Create an authorized analytics service object.
  $analytics = new Google_Service_AnalyticsReporting($client);

  // Call the Analytics Reporting API V4.
  $response = getReport($analytics);

  // Print the response.
  //printResults($response);

} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}


function getReport(&$analytics) {

  if (isset($_POST['submit'])) {
    $viewId = $_POST['viewId'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $metrics = $_POST['metrics'];
    $dimensions = $_POST['dimensions'];
    $sort = $_POST['sort'];
    $filters = $_POST['filters'];
    $segmentValue = $_POST['segment'];
    $samplingLevel = $_POST['samplingLevel'];
    $includeEmptyRows = $_POST['includeEmptyRows'];
    $startIndex = $_POST['startIndex'];
    $maxResults = $_POST['maxResults'];
    $metrics = explode(',', $metrics);
    $dimensions = explode(',', $dimensions);
    $metricArray = array();
    $prefix = '';

    // Replace with your view ID. E.g., XXXX.
    // $VIEW_ID = "20527406";

    $VIEW_ID = $viewId;
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    if (!empty($startDate) && !empty($endDate)) {
      // Create the DateRange object.
      $dateRange = new Google_Service_AnalyticsReporting_DateRange();
      $dateRange->setStartDate($startDate);
      $dateRange->setEndDate($endDate);
      $request->setDateRanges($dateRange);
    }

    if (!empty($metrics)) {
      foreach($metrics as $key => $value) {
        if (!empty($value)) {
          $id = explode(':', $value);
          $id = $id[1];
          // Create the Metrics object.
          $metricValue = new Google_Service_AnalyticsReporting_Metric();
          $metricValue->setExpression($value);
          $metricValue->setAlias($id);
          $metricArray[] = $metricValue;
        }
      }
    }

    if (!empty($dimensions)) {
      foreach($dimensions as $key => $value) {
        //Create the Dimensions object.
        $dimensionValue = new Google_Service_AnalyticsReporting_Dimension();
        $dimensionValue->setName($value);
        $dimensionArray [] = $dimensionValue;

        if (!empty($segmentValue)) {
          // Create the segment dimension.
          $segmentDimensions = new Google_Service_AnalyticsReporting_Dimension();
          $segmentDimensions->setName("ga:segment");
          $segment = new Google_Service_AnalyticsReporting_Segment();
          $segment->setSegmentId($segmentValue);
          $dimensionArray [] = $segmentDimensions;
        }
      }
    }
    else {
      $dimensionValue = new Google_Service_AnalyticsReporting_Dimension();
      $dimensionValue->setName('');
      $dimensionArray = '';
    }

    // Create the ReportRequest object.
    $request->setDimensions($dimensionArray);
    if (!empty($segmentValue)) {
      $request->setSegments(array($segment));
    }
    $request->setMetrics($metricArray);
    if (!empty($includeEmptyRows)) {
      if ($includeEmptyRows == 'true') {
        $request->setIncludeEmptyRows(true);
      }
    }
    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    $reports = $analytics->reports->batchGet( $body );

    return $reports;
  }
}


function printResults(&$reports) {
  for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
    $report = $reports[ $reportIndex ];
    $header = $report->getColumnHeader();
    $dimensionHeaders = $header->getDimensions();
    $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
    $rows = $report->getData()->getRows();

    for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
      $row = $rows[ $rowIndex ];
      $dimensions = $row->getDimensions();
      $metrics = $row->getMetrics();
      for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
        print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
      }

      for ($j = 0; $j < count( $metricHeaders ) && $j < count( $metrics ); $j++) {
        $entry = $metricHeaders[$j];
        $values = $metrics[$j];
        print("Metric type: " . $entry->getType() . "\n" );
        for ( $valueIndex = 0; $valueIndex < count( $values->getValues() ); $valueIndex++ ) {
          $value = $values->getValues()[ $valueIndex ];
          print($entry->getName() . ": " . $value . "\n");
        }
      }
    }
  }
}

?>
<!doctype html>
<html>
  <head>
    <title>
      Google analytics reports
    </title>
    <!-- Datepicker styling from jquery ui site -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <!-- Jquery cdn -->
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    <!-- Jquery UI cdn -->
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- Custom js import -->
    <script src="js/custom.js"></script>
  </head>
  <body>
    <div class="wrapper">
      <h1 class="text-center">Google analytics Reports</h1>
      <form class="form-horizontal" enctype="multipart/form-data" action="" method="post">
        <div class="form-group">
          <label class="col-sm-4 control-label" for="viewId">viewId: </label>
          <div class="col-sm-8"><input class="form-control" type="text" name="viewId" required /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="startDate">Start Date: </label>
          <div class="col-sm-8">
              <input type="text" class="form-control" name="startDate" id="startDate" required />
              <i class="icon glyphicon glyphicon-calendar"></i>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="endDate">End Date: </label>
          <div class="col-sm-8">
              <input type="text" class="form-control" name="endDate" id="endDate" required />
              <i class="icon glyphicon glyphicon-calendar"></i>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="metrics">Metrics: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="metrics" required /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="dimensions">Dimensions: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="dimensions" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="sort">Sort: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="sort" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="filters">Filters: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="filters" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="segment">Segment: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="segment" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="samplingLevel">samplingLevel: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="samplingLevel" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="include-empty-rows">include-empty-rows: </label>
          <div class="col-sm-8">
            <select name="includeEmptyRows" class="form-control">
              <option value="true">True</option>
              <option value="false">False</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="start-index">start-index: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="startIndex" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  for="max-results">max-results: </label>
          <div class="col-sm-8"><input type="text" class="form-control" name="maxResults" /></div>
        </div>
        <div class="form-group">
          <label class="col-sm-4 control-label"  ></label>
          <div class="col-sm-8"><input type="submit" name="submit" value="Submit" class="btn btn-primary"/></div>
        </div>
      </form>
    </div>
    <?php
      if (isset($response)) {
        echo "<pre class='output'>";
        print(json_encode($response, JSON_PRETTY_PRINT));
        echo "</pre>";
      }
    ?>
  </body>
</html>
