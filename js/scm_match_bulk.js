wp.api.loadPromise.done(function() {
	
var scmMatchBulkCompetitionID 	= document.getElementById("competitionid");
var scmMatchBulkDate 			= document.getElementById("date");
var scmMatchBulkTime 			= document.getElementById("time");
var scmMatchFormatLeague		= document.getElementById("league_format");
var scmMatchFormatKnockout		= document.getElementById("knockout_format");
var scmMatchFormatIndividual	= document.getElementById("individual_format");
var scmMatchFormatLadder		= document.getElementById("ladder_format");
var scmMatchBulkNrVsTeam		= document.getElementById("bulk_nrvsteam");

class MatchList extends List {
	constructor () {
		super();
		
		this.competitionID = -1;
		this.date = -1;
		this.time = -1;
		this.format = "unknown";
		this.nrVsTeam = 0;
		this.competitors = [];
		this.index = 0;
	}
	
	done() {
		location.replace(scm_match_bulk_globals.url_done);
	}
	
	getFormData() {
		var today = new Date();
		var validation = { valid : true, errorStr : "" };
		
		this.competitionID		= scmMatchBulkCompetitionID.value;
		this.date				= scmMatchBulkDate.value;
		this.time				= scmMatchBulkTime.value;
		if ( scmMatchFormatLeague.checked ) {
			this.format 	= 'league';
			this.nrVsTeam 	= scmMatchBulkNrVsTeam.value; 
			
			if (this.nrVsTeam < 1) {
				validation = { valid : false, errorStr : this.nrVsTeam + " : illegal input" };
			}
		}
		if ( scmMatchFormatKnockout.checked ) {
			this.format = 'knockout';
			
			validation = { valid : false, errorStr : "not supported yet" };			
		}
		if ( scmMatchFormatIndividual.checked ) {
			this.format = 'individual';
		}
		if ( scmMatchFormatLadder.checked ) {
			this.format 	= 'ladder';
			this.nrVsTeam 	= 1;
		}
		
		this.competitors = [];
		this.index = 0;
		
		return validation;
	}
	
	addEntries(jsonResponse) {
		
		var _entries = this.entries;
		var _compID  = this.competitionID;
		
		if (( this.format === 'league' ) || ( this.format === 'ladder' )) {
			var _nrVs  	 = this.nrVsTeam;
			var _competitors = this.competitors;
			jsonResponse.forEach(function(item){
				if (item.competition_id == _compID) {
					var _competitorID   = item.id;
					var _competitorName = item.title.rendered;
					var _idx            = _competitors.length;
					// iterate all _competitors to add new match-entries
					_competitors.forEach(function(competitor, idx){
						var h = 0;
						for (h = 0; h != _nrVs; h++) { 
							if ( (h+idx+_idx)%2 == 0 ) {
								var entry = new MatchEntry(
									competitor.competitorName, competitor.competitorID,
									_competitorName, _competitorID	
								);
							} else {
								var entry = new MatchEntry(
									_competitorName, _competitorID,	
									competitor.competitorName, competitor.competitorID
								);
							}
							_entries.push( entry );
						}
						
					});
					_competitors.push( { competitorName : _competitorName, competitorID : _competitorID } ); 
				}
			});
		} else if ( this.format === 'individual' ) {
			jsonResponse.forEach(function(item){
				if (item.competition_id == _compID) {
					var entry = new MatchEntry( item.title.rendered, item.id,	'', -1 );
					_entries.push( entry );
				}
			});
		}
	}
		
	render(all) {
		
		if ( this.format != 'individual' ) {
			var headers = [ "Competitor1", "Competitor2", "Date", "Time" ];
		} else {
			var headers = [ "Competitor1", "", "Date", "Time" ];			
		}
		var data    = [ this.date, this.time ];
		
		var html;
		
		html = "<tr><th></th><th>" + headers.join("</th><th>") + "</th></tr>";
		this.entries.forEach(function(item, idx){
			html += "<tr>" + item.render(idx, all, "<td>" + data.join("</td><td>") + "</td>") + "</tr>";
		});
		
		return "<table>" + html + "</table>";
	}
		
	getSelectedEntry(index) {
		var name1 				= this.entries[index].name1;
		var id1 				= this.entries[index].id1;
		var name2 				= this.entries[index].name2;
		var id2 				= this.entries[index].id2;
		var object 				= super.getSelectedEntry(index);
		
		object.title			= name1 + " - " + name2;
		object.match_date		= this.date;
		object.match_time		= this.time;
		object.competition_id	= this.competitionID;
		object.competitor_id_1	= id1;
		object.competitor_id_2	= id2;
		
		return object;
	}

	populatePage(this_list, pagenumber, successHandler, errorHandler) {
		var competitors = new wp.api.collections.Competitors();

		competitors.fetch( { 
			  data		: { page : pagenumber, competition_id : this_list.competitionID } 
			, success 	: successHandler 
			, error 	: errorHandler
		}); 
	}
	
	processEntry(entry, successHandler, errorHandler) {
		var match = new wp.api.models.Matches();
		
		match.save( entry
			, { success : successHandler, error : errorHandler }	
		);
	}
}


class MatchEntry extends Entry {
	
	constructor (name1, id1, name2, id2) {
		super();
		
		this.name1 = name1; 
		this.id1   = id1; 
		this.name2 = name2; 
		this.id2   = id2; 
	}
	
	render (idx, renderAll, renderedData) {
		return super.render(idx, renderAll, "<td>" + this.name1 + "</td>" + "<td>" + this.name2 + "</td>" + renderedData);
	}
	
}

var wizard = new Subject( new MatchList() );
wizard.start();

});