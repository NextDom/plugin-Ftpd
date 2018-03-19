# Enable all rules by default
all
rule 'MD002', :level => 2
rule 'MD026', :punctuation => '.,;!?'
rule 'MD041', :level => 2
# Inline HTML - this isn't forbidden by the style guide, and raw HTML use is
# explicitly mentioned in the 'email automatic links' section.
# exclude_rule 'MD033'
