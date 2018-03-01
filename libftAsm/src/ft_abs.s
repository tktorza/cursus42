section .text
global _ft_abs
_ft_abs:
	mov rax, rdi
	cmp rax, 0
	jg return
	neg rax

return:
	ret
	