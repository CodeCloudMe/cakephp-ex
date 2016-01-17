// Facebook initialization

window.fbAsyncInit = function() 
 	{
		FB.init(
				{
					appId      : '783700495040097',
					cookie     : true,  // enable cookies to allow the server to access the session
					xfbml      : true,  // parse social plugins on this page
					version    : 'v2.2' // use version 2.2
				}
			);
	};
	
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  
  
Jelly.Connections.Facebook_Login = function(Parameters)
{
// 	console.log('entering');
// 	FB.getLoginStatus( function(Facebook_Response) 
// 			{	
// 				console.log(Facebook_Response);
// 				if (Facebook_Response.status == "connected")
// 				{
// 					FB.logout( function(Logout_Response)
// 							{
// 								Continue_Facebook_Login(Parameters);
// 							}
// 						);
// 				}
// 				else
// 					Continue_Facebook_Login(Parameters);
// 			}
// 		);
	
	var Continue_Facebook_Login = function (Parameters)
	{
// 		console.log('continuing');
		// Login 
		FB.login( function(Login_Response)
				{
// 					console.log('logged in');
					// Get account information
					FB.api('/me', function(API_Response) {
					
// 							console.log('registerring');
							// Localize account information
							var ID = API_Response["id"];
							var Email = API_Response["email"];
							var First_Name = API_Response["first_name"];
							var Last_Name = API_Response["last_name"];
							var Path_To_Profile_Photo = "http://graph.facebook.com/" + ID + "/picture?type=square";
							var Namespace = Parameters["Namespace"];
			
							// Save input values
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Account_ID",
										Value: ID
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Email_Address",
										Value: Email
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "First_Name",
										Value: First_Name
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Last_Name",
										Value: Last_Name
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Path_To_Profile_Photo",
										Value: Path_To_Profile_Photo
									}
								);
							
							// Execute action.
							Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': document.getElementById(Namespace)});
						});
				}, 
				{scope:'public_profile,email'}
			);
	
	}
	
	Continue_Facebook_Login(Parameters);
};
