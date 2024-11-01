wp.api.loadPromise.done(function() {
	
var scmCompetitorBulkCat1 = document.getElementById("bulk_member_category");
var scmCompetitorBulkCompetitionID = document.getElementsByName("competitionid")[0];
var scmCompetitorBulkFormatF = document.getElementById("formatF");
var scmCompetitorBulkFormatFMN = document.getElementById("formatFMN");
var scmCompetitorBulkFormatNFM = document.getElementById("formatNFM");

class CompetitorList extends List {
	constructor () {
		super();
		
		this.memberCategory = -1;
		this.competitionID = -1;
		this.nameformat = 'f';
	}
	
	done() {
		location.replace(scm_competitor_bulk_globals.url_done);
	}
	
	getFormData() {
		var today = new Date();
		var validation = { valid : true, errorStr : "" };
		
		this.memberCategory 	= scmCompetitorBulkCat1.value;
		this.competitionID		= scmCompetitorBulkCompetitionID.value;
		if ( scmCompetitorBulkFormatF.checked ) {
			this.nameformat = 'f';
		}
		if ( scmCompetitorBulkFormatFMN.checked ) {
			this.nameformat = 'fmn';
		}
		if ( scmCompetitorBulkFormatNFM.checked ) {
			this.nameformat = 'nfm';
		}
		
		if (this.memberCategory < 0) {
			validation = { valid : false, errorStr : "select member category" };
		}
		
		return validation;
	}
	
	addEntries(jsonResponse) {
		var _entries = this.entries;
		var _format = this.nameformat;
		jsonResponse.forEach(function(item){
			var entry = new CompetitorEntry(
				  item.name + ", " + item.first_name + " " + item.middle_name 
				, item.id
				, ( (_format == 'f') 
				  ?  item.first_name 
				  : ( (_format == 'fmn') 
				    ?  item.first_name + " " + item.middle_name + " " + item.name 
					: item.name + ", " + item.first_name + " " + item.middle_name ) 
				  )
			);
			_entries.push( entry );
		});
	}
		
	render(all) {
		var headers = [ "displayed name", "type"];
		var data    = [ "Member" ];
		
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
		var display_name		= this.entries[index].display_name;
		var object 				= super.getSelectedEntry(index);
		
		object.title			= display_name;
		object.name				= '';
		object.member_id		= memberid;
		object.competition_id	= this.competitionID;
		object.competitor_type	= "member";
		
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
		var competitor = new wp.api.models.Competitors();
		
		competitor.save( entry
			, { success : successHandler, error : errorHandler }	
		);
	}
}


class CompetitorEntry extends Entry {
	
	constructor (name, id, display) {
		super();
		
		this.name = name; 
		this.id = id;
		this.display_name = display;
	}
	
	render (idx, renderAll, renderedData) {
		return super.render(idx, renderAll, "<td>" + this.name + "</td>" + "<td>" + this.display_name + "</td>" + renderedData);
	}
	
}

var wizard = new Subject( new CompetitorList() );
wizard.start();

});