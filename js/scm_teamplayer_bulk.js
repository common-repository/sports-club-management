wp.api.loadPromise.done(function() {
	
var scmTeamPlayerBulkCat1 = document.getElementById("bulk_member_category");
var scmTeamPlayerBulkCompetitorID = document.getElementById("competitorid");

class TeamPlayerList extends List {
	constructor () {
		super();
		
		this.memberCategory = -1;
		this.competitorID = -1;
		this.nameformat = 'f';
	}
	
	done() {
		location.replace(scm_teamplayer_bulk_globals.url_done);
	}
	
	getFormData() {
		var today = new Date();
		var validation = { valid : true, errorStr : "" };
		
		this.memberCategory 	= scmTeamPlayerBulkCat1.value;
		this.competitorID		= scmTeamPlayerBulkCompetitorID.value;
		
		if (this.memberCategory < 0) {
			validation = { valid : false, errorStr : "select member category" };
		}
		
		return validation;
	}
	
	addEntries(jsonResponse) {
		var _entries = this.entries;
		jsonResponse.forEach(function(item){
			var entry = new TeamPlayerEntry(
				  item.name + ", " + item.first_name + " " + item.middle_name 
				, item.id
			);
			_entries.push( entry );
		});
	}
		
	render(all) {
		var headers = [ ];
		var data    = [ ];
		
		var html;
		
		html = "<tr><th></th><th>Name</th><th>" + headers.join("</th><th>") + "</th></tr>";
		this.entries.sort(function(a, b){ 
			if (a.name < b.name) { return -1; } else { return 1; } 
		}).forEach(function(item, idx){
				html += "<tr>" + item.render(idx, all, "<td>" + data.join("</td><td>") + "</td>") + "</tr>";
		});
		
		return "<table>" + html + "</table>";
	}
		
	getSelectedEntry(index) {
		var memberid 			= this.entries[index].id;
		var object 				= super.getSelectedEntry(index);
		
		object.title			= memberid + ' in team ' + this.competitorID;
		object.member_id		= memberid;
		object.competitor_id	= this.competitorID;
		
		return object;
	}

	populatePage(this_list, pagenumber, successHandler, errorHandler) {
		var members = new wp.api.collections.Members();

		members.fetch( { 
			  data		: { page : pagenumber, member_categories : this_list.memberCategory } 
			, success 	: successHandler 
			, error 	: errorHandler
		}); 
	}
	
	processEntry(entry, successHandler, errorHandler) {
		var teamplayer = new wp.api.models.Teamplayers();
		
		teamplayer.save( entry
			, { success : successHandler, error : errorHandler }	
		);
	}
}


class TeamPlayerEntry extends Entry {
	
	constructor (name, id) {
		super();
		
		this.name = name; 
		this.id = id;
	}
	
	render (idx, renderAll, renderedData) {
		return super.render(idx, renderAll, "<td>" + this.name + "</td>" + renderedData);
	}
	
}

var wizard = new Subject( new TeamPlayerList() );
wizard.start();

});