<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Class Creation Wizard</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />

    <link rel="icon" type="image/png" href="assets/img/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/css/gsdk-base.css" rel="stylesheet" />
    
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.css" rel="stylesheet">
</head>

<body>
<div class="image-container set-full-height" style="background-image: url('assets/img/wizard.jpg')">

    <!--   Big container   -->
    <div class="container">
        <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
           
            <!--      Wizard container        -->   
            <div class="wizard-container"> 
                <div class="card wizard-card ct-wizard-green" id="wizard">
                <form action="" method="">
                <!--        You can switch "ct-wizard-azzure"  with one of the next bright colors: "ct-wizard-blue", "ct-wizard-green", "ct-wizard-orange", "ct-wizard-red"             -->
                
                    	<div class="wizard-header">
                        	<h3>
                        	   HELPING DEVELOPERS WITH <b>CLASS</b><br/>
                        	   <small>This will help you generate a class declaration for your table.</small>
                        	</h3>
                    	</div>
                    	<ul>
                            <li><a href="#server" data-toggle="tab">Server</a></li>
                            <li><a href="#table" data-toggle="tab">Table</a></li>
                            <li><a href="#analysis" data-toggle="tab">Analysis</a></li>
                            <li><a href="#class" data-toggle="tab">Class</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane" id="server">
                              <div class="row">
                                  <div class="col-sm-12">
                                    <h4 class="info-text"> Let's start with the basic database server details</h4>
                                  </div>
                                  <div class="col-sm-5 col-sm-offset-1">
                                      <div class="form-group">
                                        <label>Type</label>
                                          <select name="serverType" class="form-control" id="serverType">
                                              <option disabled="" selected="">- Server Type -</option>
                                              <option value="Afghanistan"> MySQL </option>
                                              <option value="Albania"> MS SQL </option>
                                          </select>
                                      </div>
                                  </div>
                                  <div class="col-sm-5">
                                       <div class="form-group">
                                            <label>Server Address</label><br>
                                            <input type="text" class="form-control" placeholder="IP Address" id="serverAddress">
                                          </div>
                                  </div>
                                  <div class="col-sm-5 col-sm-offset-1">
                                      <div class="form-group">
                                          <label>Username</label>
                                          <input type="text" class="form-control" placeholder="Username" id="serverUsername">
                                      </div>
                                  </div>
                                  <div class="col-sm-5">
                                      <div class="form-group">
                                          <label>Password</label>
                                              <input type="password" class="form-control" placeholder="" id="serverPassword">
                                      </div>
                                  </div>
                                  <div class="col-sm-5 col-sm-offset-1">
                                      <div class="form-group">
                                          <label>Database</label>
                                              <input type="text" class="form-control" placeholder="" id="serverDatabase">
                                      </div>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane" id="table">



                                <h4 class="info-text">Please select a table to analyze</h4>
                                <div class="row">

                                    <div class="col-sm-6 col-sm-offset-3">
                                        <div class="form-group">

                                            <div id="divTableList"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>




                            <div class="tab-pane" id="analysis">
                                <h4 class="info-text">Tell us more about facilities. </h4>
                                <div class="row">
                                    <div class="col-sm-5 col-sm-offset-1">
                                      <div class="form-group">
                                          <label>Your place is good for</label>
                                          <select class="form-control">
                                            <option disabled="" selected="">- type -</option>
                                            <option>Business</option>
                                            <option>Vacation </option>
                                            <option>Work</option>
                                          </select>
                                      </div>
                                    </div>
                                    <div class="col-sm-5">
                                      <div class="form-group">
                                          <label>Is air conditioning included ?</label>
                                          <select class="form-control">
                                            <option disabled="" selected="">- response -</option>
                                            <option>Yes</option>
                                            <option>No </option>
                                          </select>
                                      </div>
                                     </div>
                                     <div class="col-sm-5 col-sm-offset-1">
                                      <div class="form-group">
                                          <label>Does your place have wi-fi?</label>
                                          <select class="form-control">
                                            <option disabled="" selected="">- response -</option>
                                            <option>Yes</option>
                                            <option>No </option>
                                          </select>
                                       </div>
                                      </div>
                                      <div class="col-sm-5">
                                       <div class="form-group">
                                          <label>Is breakfast included?</label>
                                          <select class="form-control">
                                            <option disabled="" selected="">- response -</option>
                                            <option>Yes</option>
                                            <option>No </option>
                                          </select>
                                       </div>
                                      </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="class">
                                <div class="row">
                                    <h4 class="info-text"> Drop us a small description. </h4>
                                    <div class="col-sm-6 col-sm-offset-1">
                                         <div class="form-group">
                                            <label>Place description</label>
                                            <textarea class="form-control" placeholder="" rows="9">
                                                
                                            </textarea>
                                          </div>
                                    </div>
                                    <div class="col-sm-4">
                                         <div class="form-group">
                                            <label>Example</label>
                                            <p class="description">"The place is really nice. We use it every sunday when we go fishing. It is so awesome."</p>
                                          </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-footer">
                            	<div class="pull-right">
                                    <input type='button' class='btn btn-next btn-fill btn-success btn-wd btn-sm' name='next' value='Next' onclick="nextBtn()" />
                                    <input type='button' class='btn btn-finish btn-fill btn-success btn-wd btn-sm' name='finish' value='Finish' />
        
                                </div>
                                <div class="pull-left">
                                    <input type='button' class='btn btn-previous btn-fill btn-default btn-wd btn-sm' name='previous' value='Previous'  onclick="prevBtn()" />
                                </div>
                                <div class="clearfix"></div>
                        </div>	
                    </form>
                </div>
            </div> <!-- wizard container -->
        </div>
        </div> <!-- row -->
    </div> <!--  big container -->
   
    <div class="footer">
        <div class="container">

        </div>
    </div>
    
</div>

</body>

    <script src="assets/js/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
		
	<!--   plugins 	 -->
	<script src="assets/js/jquery.bootstrap.wizard.js" type="text/javascript"></script>
	
    <!--  More information about jquery.validate here: http://jqueryvalidation.org/	 -->
	<script src="assets/js/jquery.validate.min.js"></script>
	
	<!--  methods for manipulating the wizard and the validation -->
	<script src="assets/js/wizard.js"></script>


    <script type="text/javascript">

        var TabEnum = Object.freeze({SERVER: 1, TABLE: 2, ANALYSIS: 3, CLASS: 4});
        var $tabNum = 1;

        function nextBtn(){
            $tabNum++;
            performPageSpecificLogic();
        }

        function prevBtn(){
            $tabNum--;
            performPageSpecificLogic();
        }

        function performPageSpecificLogic(){
            if($tabNum == TabEnum.SERVER){
                //nothing is really required here
            }
            else if($tabNum == TabEnum.TABLE){
                establishDatabaseConnection();
            }
            else if($tabNum == TabEnum.ANALYSIS){
                //do analysis stuff
            }
            else if($tabNum == TabEnum.CLASS){
                //do class stuff
            }
        }

        function establishDatabaseConnection(){
            $.ajax({
                method: "POST",
                url: "response.php",
                data: {
                    action: "table",
                    serverType: $("#serverType").val(),
                    serverAddress: $("#serverAddress").val(),
                    serverUsername: $("#serverUsername").val(),
                    serverPassword: $("#serverPassword").val(),
                    serverDatabase: $("#serverDatabase").val()
                },
                dataType: "json"
            })
                .success(function( data ) {
                    var newhtml = data.html;
                    if(data.message != '') {
                        alert("Connection Attempt Made: " + data.message);
                    }
                    $("#divTableList").replaceWith(newhtml);
                });
        }

    </script>

</html>