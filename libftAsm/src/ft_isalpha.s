section .text
global _ft_isalpha
_ft_isalpha:
	cmp rdi, 65
	jl no
	cmp rdi, 122
	jg no
	cmp rdi, 90
	jg isbetween

isbetween:
	cmp rdi, 97
	jl no

yes:
	mov rax, 1
	ret

no:
	mov rax, 0
	ret