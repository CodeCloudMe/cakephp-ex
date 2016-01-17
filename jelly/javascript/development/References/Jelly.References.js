Jelly.References = 
{
	// List of simple types.
	// TODO - fix order here. 
	Simple_Types:  Simple_Types,
	
	// List of all references by id
	// TODO - Secondary path dependencies is a temporary placeholder - it gets triggered on load of the primary path dependency, that's all. hm.
	Reference_Lookups_By_Kind: {
			"Item": {}, 
			"Iterator": {},
			"URL": [],
			"Specific": {
					"Container": {
							"Item": null,
							"Dependencies": []
						},
					"Site": {
							"Item": null,
							"Dependencies": []
						},
					"Path": {
							"Dependencies": {
									"Primary": null,
									"Secondary": []
								}
						}
				}
		},

	// List of all references by namespace
	References_By_Namespace: {},
	
	// Queue of references to be refreshed.
	References_To_Refresh: {},
	
	// When true, prevents registrations via the Register call.
	// TODO: Doesn't seem to do anything....
	No_Register: false,
	
	// Incrementing index of global references, updated by Create_Global_Reference.
	Current_Global_Reference_Index: 0,
	
	// Container progress bar
	Container_Progress_Bar: null,
	Container_Progress_Bar_Interval: null
};

head.load(
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Register.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Clean_References.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Remove_Reference.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Element_Exists.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Get_Reference_For_Element.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Create_Reference.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Create_Global_Reference.js',
		
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Trigger_Refresh.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh_All.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Fill.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Block_Refresh.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Release_Refresh.js',

		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh_Document_Title.js',	
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh_Current_Path.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh_Site_Icon.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Refresh_Site.js',
		
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Get_Input_Values_For_Action.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Get_Local_Values_For_Namespace.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Get_Iterator_Parameters.js',
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.Set_Iterator_Parameters.js'

	);