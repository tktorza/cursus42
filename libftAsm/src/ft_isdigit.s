section .text
global _ft_isdigit
_ft_isdigit:
	cmp rdi, 48
	jl no
	cmp rdi, 57
	jg no

yes:
	mov rax, 1
	jmp return

no:
	mov rax, 0
	jmp return

return:
	ret