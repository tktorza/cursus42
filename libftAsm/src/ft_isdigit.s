section .text
global _ft_isdigit
_ft_isdigit:
	cmp rdi, 48
	jl no
	cmp rdi, 57
	jg no
	mov rax, 1
	ret
no:
	mov rax, 0
	ret