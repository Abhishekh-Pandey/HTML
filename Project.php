<?php
ob_start();
ini_set('display_errors',0);
include ('../Includes/Functions.php');
include('../Common.php');
require_once ('../../BL/UserManager.php');
require_once ('../../BL/InformationManager.php');
require_once ('../../BL/InstituteManager.php');
include_once('../Config/inc_path.php');
include_once('../Includes/ConstantArray.php');
$objUserManager = new UserManager();
$objInstituteManager = new InstituteManager();
$objInformationManager = new InformationManager();
require_once ('../../BL/DynamicTabCommonFuncManager.php');
$objDynamicTabCommonFuncManager = new DynamicTabCommonFuncManager();

//echo "<pre>"; print_r($_SESSION); die;
$intParentInsId =  $_SESSION['PARENT_INSTITUTE_ID'];
$intInsId =  $_SESSION['INSTITUTE_ID'];
$strInsName =  $_SESSION['INSTITUTE_NAME'];
$strInsLogoExt =  $_SESSION['INSTITUTE_LOGO'];
$intInsTypeId =  $_SESSION['INSTITUTE_TYPE_ID'];
$strInsTypeName =  $_SESSION['INSTITUTE_TYPE_NAME'];
$strCourseName =  $_SESSION['INSTITUTE_COURSE_NAME'];  
$strBreakupName =  $_SESSION['INSTITUTE_BREAKUP_NAME'];
$strUserTitle =  $_SESSION['INSTITUTE_USER_TITLE'];
$os =  $_SESSION['OS'];
$headingColour=$_SESSION['HEADING_COLOUR'];
$subHeadingColour=$_SESSION['SUB_HEADING_COLOUR'];
$totalColour=$_SESSION['TOTAL_COLOUR'];
$whiteHeadingColour=$_SESSION['WHITE_HEADING_COLOUR'];
$dtInsLicenseExpDate =  $_SESSION['LICENSE_END_DATE'];
$title =  $_SESSION['TITLE'];

$strLoginType =  $_SESSION['LOGIN_TYPE'];
$intUserId =  $_SESSION['ID'];
$userType =  $_SESSION['TYPE'];
$intUserTypeId =  $_SESSION['USER_TYPE_ID'];
$strUserName =  $_SESSION['USER_NAME'];
$strUserLoginEmailId =  $_SESSION['USER_EMAIL_ID'];
$dtUserDOB =  $_SESSION['USER_DOB'];
$strUserMobNo =  $_SESSION['USER_MOBILE_NO'];
$strUserImgExt =  $_SESSION['IMAGE_EXT'];

//echo $intParentInsId;
$status = 'Active';
$isDefault='No';

$arrDesignation=$objUserManager->GetDesignationData($intParentInsId); 
$status = 'Active';
$isDefault = 'Yes';
$paramArray = GetQueryStringParameters();
(isset($paramArray['action']))? $action=$paramArray['action'] : $action="";
(isset($paramArray['msg']))? $msg=$paramArray['msg'] : $msg="";
$flag=$paramArray['flag'];

$IsSuperAdmin = false;
if(isset($_SESSION["_HEAD_ID_"]) && ($_SESSION["_HEAD_ID_"] == "_SCHOOLSUPERADMIN_"))
{
	$IsSuperAdmin = true;
	$_SESSION['Add'] = 'Yes';
	$_SESSION['Edit'] = 'Yes';
	$_SESSION['Delete'] = 'Yes';
}
if(!$IsSuperAdmin)
{
	$currentPagePerm = CheckPermission();
}
$arrInsSearchData = array('OS'=>$os, 'TYPE'=>$intInsTypeId, 'COUNTRY'=>'', 'STATE'=>'', 'STATUS'=>$status, 'NAME'=>'', 'DEFAULT'=>$isDefault, 'INS_ID'=>$intInsId, 'USER_TYPE'=>$userType, 'USER_ID'=>$intUserId, 'PARENT_INS_ID'=>$intParentInsId);
$arrInstituteList = $objInstituteManager->GetInstitute($arrInsSearchData);

if($strLoginType=='ADMIN')
{
	$arrInstituteList = $arrInstituteList;
}
else
{
	$arrInstituteList = $arrInstituteList['arrChildInsData'];
}

$arrData=array('intParentInstituteId'=>$intParentInsId,'intInsTypeId'=>$intInsTypeId,'intInsUserId'=>'','intDesignationId'=>'',
'strDesignationType'=>'','strStatus'=>'Active','userName'=>'','intRank'=>'','intInsId'=>'','intDeviceId'=>'','strGender'=>'','strOrderBy'=>'','flag'=>'');
$arrEmployeeData=$objUserManager->GetEmployeeData($arrData); 

//echo "<pre>";print_r($arrData);die;
switch($action)
{
	case 'Search':
		//echo "<pre>";print_r($_POST);die;
		$status=(isset($_POST['status'])) ? $_POST['status'] : $paramArray['status'];	
		$arrSearch=array("intParentInsId"=>$intParentInsId,"intInsId"=>$intInsId,"intUserId"=>$intUserId,
		"IsSuperAdmin"=>$IsSuperAdmin,"strStatus"=>$status);
		$arrGetProjectDetails=$objUserManager->GetAllProjectByInsId($arrSearch);
		
		$action="";
	break;
	
	case 'InsertUpdate':
	if($_SESSION['Add'] == 'Yes' || $_SESSION['Edit'] == 'Yes')
	{
		//echo "<pre>";print_r($_POST);die;
		$strStatus=$paramArray['status'];
		//echo "<pre>"; print_r($paramArray); die;
		$projectName=$_POST['projectName'];
		$strProjectType=$_POST['strProjectType'];
		$projectTextarea=$_POST['projectTextarea'];
		$proEstimatedStartDate=$_POST['proEstimatedStartDate'];
		$proEstimatedEndDate=$_POST['proEstimatedEndDate'];
		$proStartDate=$_POST['proStartDate'];
		$proEndDate=$_POST['proEndDate'];
		$intDepartmentId=$_POST['intDepartmentId'];
		$intEmployeeId=$_POST['intEmployeeId'];
		//$status='Running';
		$intProMemberId=$_POST['EmployeeMultiple'];
		$arrProjectMemberDetails= array();
		$intProjectId=$paramArray['intProjectId'];
		
	
		$arrProjectDetails=array('intParentInsId'=>$intParentInsId,'intEmployeeId'=>$intEmployeeId,'projectName'=>$projectName,
		'strProjectType'=>$strProjectType,
		'projectTextarea'=>$projectTextarea,'proEstimatedStartDate'=>$proEstimatedStartDate,'proEstimatedEndDate'=>$proEstimatedEndDate,
		'proStartDate'=>$proStartDate,'proEndDate'=>$proEndDate,'intDepartmentId'=>$intDepartmentId,'intInstituteId'=>$intInsId,'status'=>$status,
		'intProjectId'=>$intProjectId);
		
		if(ApexCount($intProMemberId)>0)
		{
			foreach($intProMemberId as $intProMemberIdVal)
			{
				$arrProjectMemberDetails[]=array('intParentInsId'=>$intParentInsId,'intInstituteId'=>$intInsId,'intProMemberId'=>$intProMemberIdVal);
			}	
		}
			//echo "<pre>";print_r($_POST);
			//echo "<pre>";print_r($paramArray);
			//	echo "<pre>";print_r($arrProjectDetails);
			//echo "<pre>";print_r($arrProjectMemberDetails);die;
		$arrProjectList=$objUserManager->InsertUpdateProjectDetails($arrProjectDetails,$arrProjectMemberDetails);
		if($arrProjectList>0)
		{
			header("location:Project.php?urlstring=". EncryptURL("action=Search&status=".$strStatus."&msg=Project have been added successfully"));
		}
		else
		{	
			header("location:Project.php?urlstring=". EncryptURL("action=Search&status=".$strStatus."&msg=error"));
		}
	}
	else
	{
		echo "Permission Denied";
	}
	break;
	
	case 'Edit':
	//echo "<pre>"; print_r($paramArray); die;
	 	$action=$paramArray['action'];
		$intProjectId=$paramArray['intProjectId']; 
		$arrProjectDetails=array('action'=>$action,'intProjectId'=>$intProjectId);
		$arrProjectList=$objUserManager->InsertProject($arrProjectDetails,'');
		//echo "<pre>"; print_r($arrProjectList);
	break;
	
	case 'Update':
	 	$action=$paramArray['action'];
		$intProjectId=$paramArray['intProjectId']; 
		$projectName=$_POST['projectName'];
		$projectTextarea=$_POST['projectTextarea'];
		$proEstimatedStartDate=$_POST['proEstimatedStartDate'];
		$proEstimatedEndDate=$_POST['proEstimatedEndDate'];
		$proStartDate=$_POST['proStartDate'];
		$proEndDate=$_POST['proEndDate'];
		$intdepartmentId=$_POST['department_id'];
		$employeeSingleId=$_POST['EmployeeSingleId'];
		list($empId,$desgId,$intInstituteIdByEmp)=explode('@',$employeeSingleId);
		$status=$_POST['status'];
		$intProMemberId=$_POST['EmployeeMultiple'];
		//echo "<pre>"; print_r($intProMemberId); 
		$arrProjectMemberDetails= array();
		
		$arrProjectDetails=array('action'=>$action,'intParentInstituteId'=>$intParentInstituteId,'intInstituteId'=>$intInstituteId,'projectName'=>$projectName,
								 'projectTextarea'=>$projectTextarea,'proEstimatedStartDate'=>$proEstimatedStartDate,'proEstimatedEndDate'=>$proEstimatedEndDate,
								 'proStartDate'=>$proStartDate,'proEndDate'=>$proEndDate,'intdepartmentId'=>$intdepartmentId,'empId'=>$empId,
								 'status'=>$status,'intProjectId'=>$intProjectId);
		
		//echo "<pre>"; print_r($arrProjectDetails);die;
		
		if(count($intProMemberId)>0)
		{
			foreach($intProMemberId as $intProMemberIdVal)
			{
				$arrProjectMemberDetails[]=array('intParentInstituteId'=>$intParentInstituteId,'intInstituteId'=>$intInstituteId,'intProMemberId'=>$intProMemberIdVal);
			}	
		}
		
		$arrProjectList=$objUserManager->InsertProject($arrProjectDetails,$arrProjectMemberDetails);
		if($arrProjectList)// if document created successfully
		{
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=update"));
		}
		else	
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=update"));
		

	break;
	
	case 'ProjectIssue':
	$intProjectId=$paramArray['intProjectId'];
	$dtProjectDate=$_POST['projectDate'];
	$strProjectDetails=$_POST['projectDetails'];
	$strProjectComment=$_POST['projectComment'];
	$strProjectResolution=$_POST['projectResolution'];
	$flag="INSERTISSUE";
	
	//$data=array($intProjectId,$dtProjectDate,$strProjectDetails,$strProjectComment,$strProjectResolution,$intEmployeeId,$intInstituteId,$intParentInstituteId);
	//echo "<pre>"; print_r($data);die;
	$intIssueId=$objUserManager->InsertUpdateProjectIssue($intProjectId,$dtProjectDate,$strProjectDetails,$strProjectComment,$strProjectResolution,$intEmployeeId,$intInstituteId,$intParentInstituteId,$flag,'');
	
		if($intIssueId > 0)// if document created successfully
		{
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=insert"));
		}
		else	
		{
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=insertFail"));
		}
	break;
	case 'EditProjectIssue':
	//echo "<pre>"; print_r($paramArray);
	//echo "<pre>"; print_r($_POST);die;
	$dtProjectDate=$_POST['projectDate'];
	$strProjectDetails=$_POST['projectDetails'];
	$strProjectComment=$_POST['projectComment'];
	$strProjectResolution=$_POST['projectResolution'];
	$intProjectId=$paramArray['intProjectId'];
	$intProjectIssueId=$paramArray['intProjectIssueId'];
	$intEmployeeId=$paramArray['issueReportedEmpId'];
	$intInstituteId=$paramArray['intInstituteId'];
	$flag="UPDATEISSUE";
	
		$arrIssueEditReport=$objUserManager->InsertUpdateProjectIssue($intProjectId,$dtProjectDate,$strProjectDetails,$strProjectComment,$strProjectResolution,$intEmployeeId,$intInstituteId,'',$flag,$intProjectIssueId);

		if($arrIssueEditReport)// if document created successfully
		{
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=update"));
		}
		else	
		{
			header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=updateFail"));
		}
	
	break;
	
	case 'DeleteIssue':
	//echo "<pre>"; print_r($paramArray);die;
	$intProjectId=$paramArray['intProjectId'];
	$intProjectIssueId=$paramArray['intProjectIssueId'];
	$intEmployeeId=$paramArray['issueReportedEmpId'];
	$intInstituteId=$paramArray['intInstituteId'];
	$intDeleteReport=$objUserManager->deleteIssuesByIssueIdEmpIdInsIdProId($intProjectIssueId,$intProjectId,$intEmployeeId,$intInstituteId);
	if($intDeleteReport)// if document created successfully
	{
		header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=delete"));
	}
	else	
	{
		header("location:CreateNewProject.php?urlstring=". EncryptURL("msg=deleteFail"));
	}

	break;
	
	case 'excelExport':
		$intProjectId=$paramArray['intProjectId'];
		$intEmployeeId=$paramArray['intEmployeeId'];
		$intInstituteId=$paramArray['intInstituteId'];
		$dtIssueDate=$paramArray['dtIssueDate'];
		
		$arrProjectIssueXlsData=$objUserManager->getProjectIssueDataByProIdInsIdEmpId($intProjectId,$intEmployeeId,$intInstituteId,$dtIssueDate);
		$body='';
		$body.='<table width="100%" border="0" class="form-common" style="background-color:#dff7f3">
				<tr style="background-color:#91afb8">
					<td width="2%" class="head-1">S.No.</td>
					<td width="12%" class="head-1">Date</td>
					<td width="6%" class="head-1">Issue</td>
					<td width="8%" class="head-1">Comment</td>
					<td width="8%" class="head-1">Resolution</td>
					<td width="8%" class="head-1">Issue Created By</td>
				</tr>';
			$index=1;
			foreach($arrProjectIssueXlsData as $arrProjectIssueDataVal )
			{
			$body.='<tr">
						<td>'.$index++.'</td>
						<td>'.$arrProjectIssueDataVal->ISSUE_DATE.'</td>
						<td>'.$arrProjectIssueDataVal->ISSUE_DETAILS.'</td>
						<td>'.$arrProjectIssueDataVal->ISSUE_COMMENT.'</td>
						<td>'.$arrProjectIssueDataVal->ISSUE_RESOLUTION.'</td>
						<td>'.$arrProjectIssueDataVal->EMP_FIRST_NAME.'&nbsp;'. $arrProjectIssueDataVal->EMP_LAST_NAME.'</td>
					</tr>';
			}
		$body.='</table>';
		$filename="ProjectIssuesReport.xls";
		excelReport($body, $filename);
		exit();	
	break;
	
	
	case'DeleteMember':
		//echo '<pre>';print_r($paramArray);die;
		$intProjectUserId=$paramArray['intProjectMemId'];
		$intProjectId=$paramArray['intProjectId'];
		$arrProjectEmpMemberDelReport=$objUserManager->DeleteProjectMemberByUserId($intProjectUserId,$intProjectId);
		if($arrProjectEmpMemberDelReport)
		{
			header("location:Project.php?urlstring=". EncryptURL("action=AddEditProject&intProjectId=".$intProjectId."&status=".$status."&msg=Member Removed Successfully"));
		}
		else	
		{
			header("location:Project.php?urlstring=". EncryptURL("action=AddEditProject&intProjectId=".$intProjectId."&status=".$status."&msg=error"));
		}

	
	break;
}	
?>
<div class="content-wrapper">

	<section class="content-header">
		<h1>  
			<i class="fa fa-info-circle"></i>
			<small>Manage project's</small>
		</h1>
		 <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="../Dashboard/UserHome.php"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="breadcrumb-item"><a href="#">Employee Task</a></li>
			<li class="breadcrumb-item active">Project</li>
		 </ol>
	</section>

	<style>
	/* CSS used here will be applied after bootstrap.css */
	.nav-wizard {
	margin-bottom:20px;
	width:100%
	}
	.nav-wizard > li {
	float: left;
	}
	.nav-wizard > li > a {
	position: relative;
	background-color: #ff851b;
	font-size:13px;
	line-height:19px;
	padding:13px 10px;
	}
	.nav-wizard > li.blue > a, {
	background-color: #ff851b;
	color:#1b2ae0cf;
	}
	.nav-wizard > li > a:focus {
	position: relative;
	background-color: #ff851b;
	}
	
	.nav-wizard > li > a .badge {
	margin-left: 4px;
	color: #ffffff;
	background-color: #073c8d;
	}
	.nav-wizard > li > a .badge.badge-step {
	color: #000000;
	border-radius: 50%;
	padding: 3px 5px;
	background-color: #ffffff;
	}
	.nav-wizard > li.active > a .badge.badge-step {
	color: #000000;
	border-radius: 50%;
	padding: 3px 5px;
	background-color: #ffffff;
	}
	.nav-wizard > li:not(:first-child) > a {
	padding-left: 26px;
	}
	.nav-wizard > li:not(:first-child) > a:before {
	width: 0px;
	height: 0px;
	border-top: 20px inset transparent;
	border-bottom: 20px inset transparent;
	border-left: 20px solid #ffffff;
	position: absolute;
	content: "";
	top: 0;
	left: 0;
	}
	.nav-wizard > li:not(:last-child) > a {
	margin-right: 4px;
	}
	.nav-wizard > li:not(:last-child) > a:after {
	width: 0px;
	height: 0px;
	border-top: 20px inset transparent;
	border-bottom: 20px inset transparent;
	border-left: 20px solid #ff851b;
	position: absolute;
	content: "";
	top: 0;
	right: -20px;
	z-index: 2;
	}
	/*.nav-wizard > li:first-child > a {
	border-top-left-radius: 4px;
	border-bottom-left-radius: 4px;
	}*/
	/*.nav-wizard > li:last-child > a {
	border-top-right-radius: 4px;
	border-bottom-right-radius: 4px;
	}*/
	.nav-wizard > li.done:hover > a,
	.nav-wizard > li:hover > a {
	background-color: #ff851b;
	color: #ffffff;
	}
	.nav-wizard > li.done:hover > a:before,
	.nav-wizard > li:hover > a:before {
	border-right-color: #ff851b;
	color: #ffffff;
	}
	.nav-wizard > li.done:hover > a:after,
	.nav-wizard > li:hover > a:after {
	border-left-color: #ff851b;
	color: #ffffff;
	}
	.nav-wizard > li.done > a {
	background-color: #ff851b;
	color: #ffffff;
	}
	.nav-wizard > li.done > a:before {
	border-right-color: #ff851b;
	}
	.nav-wizard > li.done > a:after {
	border-left-color: #ff851b;
	color: #ffffff;
	}
	.nav-wizard > li.active > a,
	.nav-wizard > li.active > a:hover,
	.nav-wizard > li.active > a:focus {
	color: #ffffff;
	background-color: #00a65a;
	}
	.nav-wizard > li.active > a:after {
	border-left-color: #00a65a;
	}
	.nav-wizard > li.active > a .badge {
	color: #073c8d;
	background-color: #00a65a;
	}
	
	@media (max-width: 768px) {
	.nav-wizard.nav-justified > li > a {
	/*border-radius: 4px;*/
	margin-right: 0;
	}
	.nav-wizard.nav-justified > li > a:before,
	.nav-wizard.nav-justified > li > a:after {
	border: none !important;
	}
	}
	</style>
	<section class="content">   
		<!-- Main content -->
		<?php 
		if($msg!="")
		{
			if($msg=='error1')
			{
				?>
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
					<strong>Please Select Correct File Format While Uploading</strong>.
				</div>
				<?php
			}
			if($msg=='error')
			{
				?>
				<div class="alert alert-danger ">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
					<strong>Error Occured</strong>.
				</div>
				<?php
			}
			else
			{
				?>
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
					<strong><?php echo $msg; ?></strong>.
				</div>
				<?php
			}
		}
		?>
		
		<?php
		if($action=='')
		{
		?>
            <div class="register-box-body">  
				<?php 
                if($_SESSION['Add'] == 'Yes')
                {
                ?>
                    <a href="Project.php?urlstring=<?php echo EncryptURL('action=AddEditProject'); ?>" class="btn btn-warning btn-xs pull-right"><i class="fa fa-plus"></i></a>

                <?php 
                }
                ?>
                <form action="Project.php?urlstring=<?php echo EncryptURL("action=Search") ?>" method="post" enctype="multipart/form-data"  id="insertUpdate" name="insertUpdate">
                    <div class="row">
                        <div class="colo-md-3 col-sm-3">
                           <div class="form-group has-feedback" >
                                <label for="Objective">Status <span style="color:#FF0000">*</span></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="Running" <?php if($status=='Running') echo 'selected';?>>Running</option>
                                    <option value="On-Hold" <?php if($status=='On-Hold') echo 'selected';?>>On-Hold</option>
                                    <option value="Completed" <?php if($status=='Completed') echo 'selected';?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="colo-md-1 col-sm-1">
                            <div class="form-group has-feedback" >
                                <label for="Objective">&nbsp;</label>
                                <div>
                                <button type="submit" class="btn btn-warning btn-sm" style="float:right">Search</button>
                                </div>
                            </div>
                        </div>	
                    </div>	
                </form>
            </div>
			<section class="content-header">
			  <h3>
				Project Listing
			  </h3>
			</section>
			<div class="box-body table-responsive no-padding">
				<table width="100%" cellspacing="0" cellpadding="0" class="table table-hover table-bordered">
					<tr width="100%" style="background-color:<?php echo $headingColour ;?>;color:#333">
						<th width="3%">#</th>
						<th width="4%">Name</th>
						<th width="24%">Status</th>
						<th width="24%">Emp Start Date</th>
						<th width="24%">Emp End Date</th>
						<th width="7%">Start Date</th>
						<th width="7%">End Date</th>
						<th width="7%">Manager</th>
						<th width="7%">Members</th>
						<th  style="text-align:center" width="14%">Action</th>
					</tr>
					<?php
					//echo "<pre>"; print_r($arrGetProjectDetails); 	
					if(ApexCount($arrGetProjectDetails)>0)
					{
						if(isset($arrGetProjectDetails))
						{
							$index=1;
							//echo '<pre>'; print_r($arrGetProjectDetails); die;
							foreach($arrGetProjectDetails as $arrProjectVal)
							{
							?>
								<tr>
                                    <td><?php echo $index++; ?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_NAME ;?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_STATUS; ?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_ESTIMATED_START_DATE; ?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_ESTIMATED_END_DATE ;?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_START_DATE; ?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_END_DATE; ?></td>
                                    <td><?php echo $arrProjectVal->PROJECT_MANAGER; ?></td>
                                        <?php
                                        $flag='count';
                                        $totalMember=0;
                                        /*$arrGetProjectMembersByProId = $objTaskManager->GetProjectMembersByProId($arrProjectVal->PROJECT_ID,$flag);
                                        if(ApexCount($arrGetProjectMembersByProId)>0)
                                        {
                                            $totalMember = $arrGetProjectMembersByProId[0]->TOTAL_MEMBER;
                                        }*/
                                        ?>
                                        <td><?php echo $totalMember; ?></td>
                                        <td>
                                      <?php  
									  if($_SESSION['Edit'] == 'Yes')
                                       {?>
                                        <a href="Project.php?urlstring=<?php echo EncryptURL('action=AddEditProject&intProjectId='.$arrProjectVal->PROJECT_ID.'&status='.$strStatus.'&intParentInsId='.$intParentInsId.'&intInsId='.$intInsId.'&intUserId='.$intUserId.'&IsSuperAdmin='.$IsSuperAdmin);?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>	
                                      <?php }?>

                                            													
                                         	 	
		
	
										 <a href="CreateProjectPlanning.php?urlstring=<?php echo EncryptURL('action='.'&flag='.$Manager.'&status='.$strStatus.'&intProjectId='.$arrProjectVal->PROJECT_ID.'&intProjectManagerId='.$arrProjectVal->PROJECT_MANAGER_EMP_ID.'&intManagerId='.$arrProjectVal->PROJECT_MANAGER_EMP_ID.'&strProjectName='.$arrProjectVal->PROJECT_NAME.'&strProjectManagerName='.$arrProjectVal->PROJECT_MANAGER.'&strProjectStatus='.$arrProjectVal->PROJECT_STATUS.'&proStartDate='.$arrProjectVal->PROJECT_START_DATE.'&proEndDate='.$arrProjectVal->PROJECT_END_DATE.'&intInstituteId='.$arrProjectVal->INSTITUTE_ID);?>" class="btn btn-warning btn-sm" target="_blank">Planning</a>
											
                                            <a href="CreateNewProject.php?urlstring=<?php echo EncryptURL('intProjectId='.$arrProjectVal->PROJECT_ID.'&strProjectName='.$arrProjectVal->PROJECT_NAME.'&intInstituteId='.$arrProjectVal->INSTITUTE_ID);?>" class="btn btn-warning btn-sm" target="_blank">Issues</a>
											
                                            <a href="ProjectTaskInformation.php?urlstring=<?php echo EncryptURL('action=&intProjectId='.$arrProjectVal->PROJECT_ID.'&projectName='.$arrProjectVal->PROJECT_NAME.'&intProjectManagerId='.$arrProjectVal->PROJECT_MANAGER_INSTITUTE_USER_ID.'&intDesignationId='.$arrProjectVal->USER_TYPE_ID);?>" class="btn btn-warning btn-sm">Monitoring</a> 
                                            <a href="ProjectReport.php?urlstring=<?php echo EncryptURL('action=ProjectReport&intProjectId='.$arrProjectVal->PROJECT_ID.'&intProjectManagerId='.$arrProjectVal->PROJECT_MANAGER_EMP_ID.'&intInstituteId='.$arrProjectVal->INSTITUTE_ID);?>" class="btn btn-warning btn-sm">Progress Report</a>
                                        </td>
								</tr>
							<?php
							}
						}
					}
					else
					{
					?>
                    	<tr>
                        	<td colspan="10" style="color:#C10003">No Data Found</td>
                        </tr>
                    <?php	
					}
					
					?>
				</table>	
			</div>	
			<?php
		}
		if($action=='AddEditProject')
		{
			//echo "<pre>";print_r($paramArray);die;
			$status=$paramArray['status'];
			$intProjectId=$paramArray['intProjectId'];
			$arrSearch=array("intParentInsId"=>$intParentInsId,"intProjectId"=>$paramArray['intProjectId']);
			$arrProject=$objUserManager->GetProjectDetailsById($arrSearch);
			//echo "<pre>"; print_r($paramArray);die;
			?>
            <div class="box">
                <form action="Project.php?urlstring=<?php echo EncryptURL("action=InsertUpdate&intProjectId=".$intProjectId."&status=".$status) ?>" method="post" enctype="multipart/form-data"  name="Insert">
                    <div class="box-body"> 
                        <h5 align="center">
                            <div class="row">
                                <div class="box-header with-border colo-md-12 col-sm-12" style="background:<?php echo $headingColour;?>">						
                                    <h5 class="box-title"><font color="#333">Add Project</font></h5>
                                </div>
                            </div>
                        </h5>
                        <div class="row" style="margin-top:20px">
                            <div class="colo-md-6 col-sm-6">
                                <div class="row">
                                    <div class="colo-md-12 col-sm-12">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Name <span style="color:#FF0000">*</span></label>
                                            <input class="form-control" type="text" value="<?php echo $arrProject[0]->PROJECT_NAME;  ?>" name="projectName" id="projectName" required />
                                        </div>
                                    </div>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Type<span style="color:#FF0000">*</span></label>
                                            <select required name="strProjectType" id="strProjectType" class="form-control">	
                                                <option value="">Select</option>	
                                                <option value="Regular" <?php if($arrProject[0]->PROJECT_TYPE=="Regular") echo "selected"; ?>>Regular</option>	
                                                <option value="Project" <?php if($arrProject[0]->PROJECT_TYPE=="Project") echo "selected"; ?>>Project</option>	
                                                <option value="Service" <?php if($arrProject[0]->PROJECT_TYPE=="Service") echo "selected"; ?>>Service</option>	
                                            </select>
                                        </div>
                                    </div>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Start Date<span style="color:#FF0000">*</span></label>
                                            <input required class="form-control" type="date" value="<?php echo $arrProject[0]->PROJECT_START_DATE;  ?>" name="proStartDate" id="proStartDate" onchange="projectStartDateValidation();" />
                                        </div>
                                    </div>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project End Date<span style="color:#FF0000">*</span></label>
                                            <input required class="form-control" type="date" value="<?php echo $arrProject[0]->PROJECT_END_DATE;  ?>" name="proEndDate" id="proEndDate" onchange="projectEndDateValidation();"/>
                                        </div>
                                    </div>
                                    <?php 
									if($intProjectId=="")
									{
									?>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective"><?php echo $strInsTypeName; ?><span style="color:#FF0000">*</span></label>
                                            <select required name="intInstituteId" id="intInstituteId" onChange="getDepartmentByInsId(this.value,'<?php echo $intParentInsId ;?>'),GetEmpByDepartmentId(this.value)" class="form-control" >
                                                <option value="" >Select </option>			
                                                <?php
                                                    if(ApexCount($arrInstituteList)>0)
                                                    {
                                                        foreach($arrInstituteList as $arrInstituteVal)
                                                        {
                                                            ?>
                                                            <option value="<?php echo $arrInstituteVal->INSTITUTE_ID; ?>" <?php if($arrInstituteVal->INSTITUTE_ID==$arrProject[0]->INSTITUTE_ID) echo "selected"; ?>><?php echo $arrInstituteVal->INSTITUTE_NAME; ?></option>
                                                            
                                                            <?php
                                                        }
                                                    }
                                                
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<?php
									}
                                    if($arrProject[0]->INSTITUTE_ID!='')
                                    {
                                        $arrDepartment=$objInstituteManager->GetDepartmentData($arrProject[0]->INSTITUTE_ID,$intParentInsId);
                                    }
                                    ?>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Department<span style="color:#FF0000">*</span></label>
                                            <div id="DeprtDiv">
                                                <select name="intDepartmentId" id="intDepartmentId" onchange="GetEmpByDepartmentId(this.value)" class="form-control" >
                                                    <option value="">Select</option>
                                                    <?php
                                                    if(ApexCount($arrDepartment)>0)
                                                    {
                                                        foreach($arrDepartment as $departVal)
                                                        {
                                                            ?>
                                                            <option value="<?php echo $departVal->INSTITUTE_DEPARTMENT_ID;?>" <?php if($departVal->INSTITUTE_DEPARTMENT_ID==$arrProject[0]->PROJECT_INSTITUTE_DEPARTMENT_ID) echo 'selected';?>><?php echo $departVal->DEPARTMENT_NAME;?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
									<?php
									
                                    if($arrProject[0]->PROJECT_INSTITUTE_DEPARTMENT_ID!='')
                                    {
                                        $arrData=array('intParentInsId'=>$intParentInsId, 'intDepartmentId'=>$arrProject[0]->PROJECT_INSTITUTE_DEPARTMENT_ID);
                                        $arrEmpData=$objInstituteManager->GetEmpDataDepartmentId($arrData);
                                    }
                                    ?>
                                    
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Employee<span style="color:#FF0000">*</span></label>
                                            <div id="empDepartmentDiv">
                                                <select type="text" name="intEmployeeId" id="intEmployeeId" class="form-control">	
                                                    <option value="">Select</option>	
                                                    <?php
                                                    if(ApexCount($arrEmpData)>0)
                                                    {
                                                        foreach($arrEmpData as $empVal)
                                                        {
                                                            ?>
                                                            <option value="<?php echo $empVal->INSTITUTE_USER_ID;?>" <?php if($empVal->INSTITUTE_USER_ID==$arrProject[0]->PROJECT_MANAGER_INSTITUTE_USER_ID){ echo 'selected';}?>><?php echo $empVal->USER_NAME;?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="colo-md-6 col-sm-6">
                                <div class="row">
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Estimated Start Date<span style="color:#FF0000">*</span></label>
                                            <input class="form-control" type="date" value="<?php echo $arrProject[0]->PROJECT_ESTIMATED_START_DATE;  ?>" required name="proEstimatedStartDate" id="proEstimatedStartDate" onchange="estStartDateValidation();" />
                                        </div>
                                    </div>
                                    <div class="colo-md-6 col-sm-6">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Estimated End Date<span style="color:#FF0000">*</span></label>
                                            <input class="form-control" type="date" value="<?php echo $arrProject[0]->PROJECT_ESTIMATED_END_DATE;  ?>" required name="proEstimatedEndDate" id="proEstimatedEndDate" onchange="estEndDateValidation();" />
                                        </div>
                                    </div>
                                    <div class="colo-md-12 col-sm-12">
                                       <div class="form-group has-feedback" >
                                            <label for="Objective">Project Details<span style="color:#FF0000">*</span></label>
                                            <textarea required class="form-control" rows="8" name="projectTextarea" id="projectTextarea"><?php echo $arrProject[0]->PROJECT_DETAILS;  ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        	<?php
							foreach($arrProject as $empVal)
							{
								$arrEmpId.=",".$empVal->PROJECT_MEMBER_INSTITUTE_USER_ID;
							}
							$arrEmpId=explode(",",(ltrim($arrEmpId,",")));
							//echo "<pre>"; print_r($arrEmpId); die;
							if(ApexCount($arrEmployeeData)>0)
							{
							?>
                            <div class="box-body table-responsive no-padding">
                                <table width="100%" cellspacing="0" cellpadding="0" class="table table-hover table-bordered">
                                    <tr width="100%" style="background-color:<?php echo $headingColour ;?>;color:#333">
                                        <td>&nbsp;</td>
                                        <td>#</td>
                                        <td>Employee Name</td>
                                        <td>Father Name</td>
                                        <td>Dept</td>
                                        <td>Email</td>
                                        <td>Mobile No.</td>
                                        <td>Dob</td>
                                        <td>Action</td>
                                    </tr>	
                                    <?php
                                    $i=1;
                                    foreach($arrEmployeeData as $arrEmployee)
                                    {
										if($arrEmployee->USER_TYPE_ID=='3')
										 continue;
										 //echo $arrEmpId;
									?>
                                        <tr>
                                            <td>
                                            	<input type="checkbox" name="EmployeeMultiple[]" value="<?php echo $arrEmployee->INSTITUTE_USER_ID;?>" id="Ids_<?php echo $arrEmployee->INSTITUTE_USER_ID;?>" <?php if(in_array($arrEmployee->INSTITUTE_USER_ID,$arrEmpId)) echo 'checked disabled';?><?php echo $readonlyCreatedForMe;?> <?php echo $readonlyMeInGroup;?>/>
                                                <label for="Ids_<?php echo $arrEmployee->INSTITUTE_USER_ID;?>" ></label>
                                            </td>
                                            <input type="hidden" name="Ids[]" value="<?php echo $arrEmployee->INSTITUTE_USER_ID;?>"  />
                                            <td><?php echo $i++;?></td>
                                            <td><?php echo $arrEmployee->USER_NAME."-".$arrEmployee->INSTITUTE_USER_ID;?></td>
                                            <td><?php echo $arrEmployee->FATHER_NAME;?></td>
                                            <td><?php echo $arrEmployee->DEPARTMENT_NAME;?></td>
                                            <td><?php echo $arrEmployee->USER_EMAIL_ID;?></td>
                                            <td><?php echo $arrEmployee->USER_MOBILE_NO;?></td>
                                            <td><?php echo $arrEmployee->DOB;?></td>
                                            <td>
												<?php 
                                                if(in_array($arrEmployee->INSTITUTE_USER_ID,$arrEmpId))
                                                {
                                                ?>
                                                <a href='Project.php?urlstring=<?php echo EncryptURL('action=DeleteMember&intProjectMemId='.$arrEmployee->INSTITUTE_USER_ID.'&intProjectId='.$intProjectId);?>' class="btn btn-warning btn-sm">Remove</a>
                                                <?php
                                                }
                                                ?>                                            
                                            </td>
                                        </tr>
									<?php 
                                    }
									?>
                                </table>
                            </div>
                            <?php
							}
							?>
                        </div>
                            <?php 
                            if($_SESSION['Add']=='Yes' || $_SESSION['Edit']=='Yes' )
                            {
                            ?>
                                <div class="row">
                                    <div class="colo-md-12 col-sm-12">
                                      	<button type="submit" class="btn btn-submit btn-xs" >Submit</button>
                                        <button type="reset" class="btn btn-reset btn-xs" >Reset</button>
                                        <a href="Project.php?urlstring=<?php echo EncryptURL("action=Search&status=".$status)?>" class="btn btn-cancel btn-xs">Cancel</a>
                                      
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                    </div>
                </form>
            </div>
			<script src="../../js/func_ajax.js"></script>	
            <script>		
                function getDepartmentByInsId(insId,parentInsId)
                {
                    console.log(insId);
                    console.log(parentInsId);
                    callAjax('DeprtDiv', "../Ajax/getDepartmentByInsId.php",  
                    {
                    params:"insId="+insId+"&intParentInsId="+parentInsId+"&strFunctionFlag=Yes",
                    meth:"get",
                    async:true,
                    startfunc:"s_function('DeprtDiv')",
                    endfunc:"e_function()",
                    errorfunc:"ajaxError()" 
                    }
                    );
                }
				function GetEmpByDepartmentId(val)
				{
					callAjax('empDepartmentDiv', "../Ajax/getEmpByDepartmentId.php",  
					{
					params:"intDepartmentId="+val,
					meth:"get",
					async:true,
					startfunc:"s_function('empDepartmentDiv')",
					endfunc:"e_function()",
					errorfunc:"ajaxError()" 
					}
					);
				}
            </script>
			<script language="JavaScript" type="text/javascript">
			function estEndDateValidation ()
			{
				var firstdat = document.getElementById("proEstimatedStartDate").value;
				var secondDate = document.getElementById("proEstimatedEndDate");
				var secondDat= secondDate.value;
				if(secondDat < firstdat)
				{
				alert('"Estimated project End date" should not be less than "Estimated project Start date"');
						secondDate.value = '';
						return false;
				}
				else
				{
					return true;
				}
			}
			function estStartDateValidation ()
			{
				var secondDate = document.getElementById("proEstimatedEndDate").value;
				if(secondDate!='')
				{
					var firstdate = document.getElementById("proEstimatedStartDate");
					var firstdat= firstdate.value;
					if(firstdat > secondDate)
					{
					alert('"Estimated project Start date" should not be greater than "Estimated project End  date"');
						firstdate.value = '';
						return false;
					}
					else
					{
						return true;
					}
				}
			}
				
			function projectStartDateValidation ()
			{
				var proEstimatedStartDate = document.getElementById("proEstimatedStartDate").value;
				var proEstimatedEndDate= document.getElementById("proEstimatedEndDate").value;
				var proStartDate= document.getElementById("proStartDate");
			
				var proStartDates= proStartDate.value;
				
				if(proStartDates < proEstimatedStartDate || proStartDates > proEstimatedEndDate )
				{
				alert('Pjocet End Date Should be in between Estimated start date and Estimated end date');
						proStartDate.value = '';
						return false;
				}
				else
				{
					return true;
				}
			}
			
			
			function projectEndDateValidation ()
			{
				var proEstimatedStartDate = document.getElementById("proEstimatedStartDate").value;
				var proEstimatedEndDate= document.getElementById("proEstimatedEndDate").value;
				var proEndDate= document.getElementById("proEndDate");
			
				var proEndDates= proEndDate.value;
				
				if(proEndDates < proEstimatedStartDate || proEndDates > proEstimatedEndDate )
				{
				alert('Pjocet End Date Should be in between Estimated start date and Estimated end date');
						proEndDate.value = '';
						return false;
				}
				else
				{
					return true;
				}
			}
			function GetEmployeeId(DeptId)
			{
			//alert('hii');
				callAjax("showEmpDetails","../Ajax/getEmployeeByDisgId.php", 
				{
				params:"DeptId="+DeptId,
				meth:"get",
				async:true,
				startfunc:"s_function('showEmpDetails')",
				endfunc:"e_function()",
				errorfunc:"ajaxError()" 
				}
				);
			}
			function GetAllMember(intInstituteId,DeptId)
			{
				var intInstituteId=intInstituteId;
				var DeptId=DeptId;
				
				callAjax("showAllMember","../Ajax/getAllMemberByInsDesig.php", 
				{
				params:"DeptId="+DeptId+"&intInstituteId="+intInstituteId,
				meth:"get",
				async:true,
				startfunc:"s_function('showAllMember')",
				endfunc:"e_function()",
				errorfunc:"ajaxError()" 
				}
				);
				
			}
			</script>
			<script>
			function ValidationForm()
			{
				var projectName =document.getElementById('projectName').value;  //here editlocker is form name and lockerName is input field name not id 
				var projectTextarea =document.getElementById('projectTextarea').value;
				var proEstimatedStartDate =document.getElementById('proEstimatedStartDate').value;
				var proEstimatedEndDate =document.getElementById('proEstimatedEndDate').value;
				var asignProject=document.getElementById('department_id').value;
				
				
				if(projectName == null || projectName == "")
				{
					 alert("Please Fill the Project Name");
					 return false;
				}
				if(projectTextarea == null || projectTextarea == "")
				{
					 alert("Please Fill the Project Details");
					 return false;
				}
				if(proEstimatedStartDate == null || proEstimatedStartDate == "")
				{
					 alert("Please Fill the Estimate Start Date");
					 return false;
				}
				if(proEstimatedEndDate == null || proEstimatedEndDate == "")
				{
					 alert("PPlease Fill the Estimate End Date");
					 return false;
				}
				if(asignProject == null || asignProject == "")
				{
					 alert("Please Fill the Asign Details");
					 return false;
				}
			}
			</script>
			<?php
		}
		?>	
	</section>
</div>
<?php
$pageMainContent = ob_get_contents();
ob_end_clean();
$pagetitle = $title." : Verify Task";
include('../MasterTemplatePage.php');
?>