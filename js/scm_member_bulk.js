wp.api.loadPromise.done(function() {
	
var scmMemberBulkFile = document.getElementById("filename");

class MemberList extends List {
	constructor () {
		super();
		
		this.file		= "";
		this.headers	= [];
	}
	
	done() {
		location.replace(scm_member_bulk_globals.url_done);
	}
	
	getFormData() {
		var today = new Date();
		var validation = { valid : true, errorStr : "" };
		
		if (scmMemberBulkFile.value == "") {
			validation.valid = false;
			validation.errorStr = "Select file, please";
			
			return validation;
		}
		
		this.file = scmMemberBulkFile.files[0];
				
		return validation;
	}
	
	addEntries(fields) {
		if (fields != "") {
			var entry = new MemberEntry( fields );
			this.entries.push( entry );
		}
	}
		
	render(all) {
		var html;
		
		html = "<tr><th></th><th>" + this.headers.join("</th><th>") + "</th></tr>";
		this.entries.forEach(function(item, idx){
			html += "<tr>" + item.render(idx, all, "") + "</tr>";
		});
		
		return "<table>" + html + "</table>";
	}
		
	getSelectedEntry(index) {
		var object 		= super.getSelectedEntry(index);
		var _fields		= this.entries[index].fields;
		var mapheader	= scm_member_bulk_globals.mapheader;
		
		object["middle_name"] = ""; 
		
		this.headers.forEach(function(item, idx){
			var value = _fields[idx].trim();
			
			if (value != "") {
				if ( mapheader[item] != undefined ) {
					object[ mapheader[item] ] = _fields[idx].trim();
				}
			}
		});
		
		if ((object["name"] != undefined) && (object["first_name"] != undefined)) {
			object["title"]	= object["name"] + ", " + object["first_name"] + " " + object["middle_name"];
		}

		return object;
	}

	populate(subject) {
		var filereader = new FileReader();
		
		var _list 			= this;
			
		filereader.onerror = function(evt){
			subject.setMessage("error", "File read error");
			subject.networkAccess(false);
		};

		filereader.onload = function(evt) {
			var lines = filereader.result.split("\r\n");
			
			lines.forEach(function(item, idx){
				if (idx == 0) {
					_list.headers = lines[idx].split(",");
				} else {
					_list.addEntries(lines[idx].split(","));
				}
			});
			
			subject.setMessage("none", "");
			subject.networkAccess(false);
		};
		
		subject.setMessage("info", "Please wait, loading data...");
		subject.networkAccess(true);
		
		filereader.readAsText(this.file);
	}
	
	processEntry(entry, successHandler, errorHandler) {
		var member = new wp.api.models.Members();
		
		member.save( entry
			, { success : successHandler, error : errorHandler }	
		);
	}
}


class MemberEntry extends Entry {
	
	constructor (fields) {
		super();
		
		this.fields = fields;
	}
	
	render (idx, renderAll, renderedData) {
		return super.render(idx, renderAll, "<td>" + this.fields.join("</td><td>") + "</td>" + renderedData);
	}
	
}

var wizard = new Subject( new MemberList() );
wizard.start();

});